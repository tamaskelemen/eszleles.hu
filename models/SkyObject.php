<?php

namespace app\models;

use app\components\SkyMatcher;
use yii\db\Query;
use yii\helpers\Url;

/**
 * This is the model class for table "sky_objects".
 *
 * A deep sky object of the catalog, imported by the sky/import command.
 *
 * @property int $id
 * @property string $name catalog number, e.g. "NGC 1976"
 * @property int|null $messier
 * @property string|null $common_name
 * @property string $type type code of OpenNGC
 * @property string $constellation IAU abbreviation
 * @property double $ra right ascension in degrees (J2000)
 * @property double $dec declination in degrees (J2000)
 * @property double|null $size major axis in arc minutes
 * @property double|null $min_axis minor axis in arc minutes
 * @property double|null $magnitude visual (or blue, if that is the only one) magnitude
 * @property double|null $surface_brightness mag/arcsec², from the catalog or estimated by estimateSurfaceBrightness()
 * @property string[] $catalogs prefixes of the designations, e.g. ["M", "NGC"]
 */
class SkyObject extends \yii\db\ActiveRecord
{
    const ITEM_SEPARATOR = '|';
    const ITEM_LIMIT = 100;
    const SEARCH_LIMIT = 10;

    /** Types of OpenNGC that are not deep sky objects */
    const NON_DEEP_SKY_TYPES = ['*', '**', '*Ass', 'Nova', 'Other', 'NonEx', 'Dup'];

    /** Prefix of an alias key => how it is written, in the order the designations are listed */
    const DESIGNATION_FORMATS = [
        'M' => 'M %s',
        'C' => 'C %s',
        'NGC' => 'NGC %s',
        'IC' => 'IC %s',
        'SH2' => 'Sh2-%s',
        'B' => 'B %s',
        'VDB' => 'vdB %s',
        'LBN' => 'LBN %s',
        'LDN' => 'LDN %s',
        'PAL' => 'Pal %s',
        'ABELL' => 'Abell %s',
        'ACO' => 'ACO %s',
        'CR' => 'Cr %s',
        'MEL' => 'Mel %s',
        'TR' => 'Tr %s',
        'STOCK' => 'Stock %s',
        'BERKELEY' => 'Berkeley %s',
        'KING' => 'King %s',
        'HCG' => 'HCG %s',
        'ARP' => 'Arp %s',
        'CED' => 'Ced %s',
        'GUM' => 'Gum %s',
        'SIMEIS' => 'Simeis %s',
        'UGC' => 'UGC %s',
        'PGC' => 'PGC %s',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sky_objects';
    }

    /**
     * Mean surface brightness of an elliptical object.
     *
     * @param float|null $magnitude
     * @param float|null $majorAxis arc minutes
     * @param float|null $minorAxis arc minutes, the major one if it is not known
     * @return float|null mag/arcsec²
     */
    public static function estimateSurfaceBrightness($magnitude, $majorAxis, $minorAxis)
    {
        if ($magnitude === null || !$majorAxis) {
            return null;
        }

        $area = M_PI / 4 * ($majorAxis * 60) * (($minorAxis ?: $majorAxis) * 60);

        return round($magnitude + 2.5 * log10($area), 2);
    }

    /**
     * @param string $alias alias key, e.g. "SH2 155"
     * @return string|null e.g. "Sh2-155", null for a nickname
     */
    public static function formatDesignation($alias)
    {
        $parts = explode(' ', $alias, 2);

        if (count($parts) !== 2) {
            return null;
        }

        return sprintf(self::DESIGNATION_FORMATS[$parts[0]] ?? $parts[0] . ' %s', $parts[1]);
    }

    /**
     * @param string $catalog prefix of an alias key
     * @return string e.g. "Sh2", the prefix itself for an unknown catalog
     */
    public static function formatCatalog($catalog)
    {
        return trim(str_replace(['%s', '-'], '', self::DESIGNATION_FORMATS[$catalog] ?? $catalog));
    }

    /**
     * @return string[] every catalog number of the object, the best known first
     */
    public function getDesignations()
    {
        $aliases = (new Query())->select('alias')->from('sky_object_aliases')->where(['sky_object_id' => $this->id])->column();
        $order = array_flip(array_keys(self::DESIGNATION_FORMATS));
        $designations = [];

        foreach ($aliases as $alias) {
            $designation = self::formatDesignation($alias);

            if ($designation !== null) {
                $catalog = explode(' ', $alias)[0];
                $designations[] = [$order[$catalog] ?? count($order), $alias, $designation];
            }
        }

        usort($designations, function ($a, $b) {
            return $a[0] - $b[0] ?: strnatcmp($a[1], $b[1]);
        });

        return array_values(array_unique(array_column($designations, 2)));
    }

    /**
     * @return string the nickname, the Messier number or the catalog number
     */
    public function getDisplayName()
    {
        return $this->common_name ?: ($this->messier ? 'M ' . $this->messier : $this->name);
    }

    /**
     * @param string $type type code of OpenNGC
     * @return string
     */
    public static function getTypeName($type)
    {
        $list = [
            '*' => 'csillag',
            '**' => 'kettőscsillag',
            '*Ass' => 'csillagtársulás',
            'OCl' => 'nyílthalmaz',
            'GCl' => 'gömbhalmaz',
            'Cl+N' => 'nyílthalmaz és köd',
            'G' => 'galaxis',
            'GPair' => 'galaxispár',
            'GTrpl' => 'galaxishármas',
            'GGroup' => 'galaxiscsoport',
            'GCluster' => 'galaxishalmaz',
            'PN' => 'planetáris köd',
            'HII' => 'HII-régió',
            'DrkN' => 'sötét köd',
            'EmN' => 'emissziós köd',
            'Neb' => 'köd',
            'RfN' => 'reflexiós köd',
            'SNR' => 'szupernóva-maradvány',
            'Nova' => 'nóva',
        ];

        return $list[$type] ?? 'egyéb';
    }

    /**
     * Finds the objects that belong to the given alias keys.
     *
     * @param string[] $aliases
     * @return array lists of objects (as arrays) indexed by alias
     */
    public static function findByAliases(array $aliases)
    {
        $rows = (new Query())
            ->select(['a.alias', 'o.id', 'o.name', 'o.messier', 'o.common_name', 'o.constellation', 'o.ra', 'o.dec'])
            ->from(['a' => 'sky_object_aliases'])
            ->innerJoin(['o' => self::tableName()], 'o.id = a.sky_object_id')
            ->where(['a.alias' => $aliases])
            ->orderBy(['a.id' => SORT_ASC])
            ->all();

        $objects = [];

        foreach ($rows as $row) {
            $row['designation'] = self::getOtherDesignation($row['alias'], $row['name']);
            $objects[$row['alias']][] = $row;
        }

        return $objects;
    }

    /**
     * @param array $object
     * @return string e.g. "M 42 – Orion-köd (NGC 1976)"
     */
    public static function buildLabel(array $object)
    {
        $name = $object['designation'] ?? $object['name'];
        $label = $object['messier'] ? 'M ' . $object['messier'] : $name;

        if ($object['common_name']) {
            $label .= ' – ' . $object['common_name'];
        }

        if ($object['messier']) {
            $label .= ' (' . $name . ')';
        }

        return $label;
    }

    /**
     * @return string the sky map with this object opened, the parameter is read by sky-map.js
     */
    public function getMapUrl()
    {
        return Url::to(['/sky-map/index', 'objektum' => $this->id]);
    }

    /**
     * @param array $object
     * @return array item of the sky object input
     */
    public static function buildItem(array $object)
    {
        $designation = $object['designation'] ?? null;

        return [
            'id' => (int)$object['id'],
            'value' => $object['id'] . ($designation === null ? '' : self::ITEM_SEPARATOR . $designation),
            'label' => self::buildLabel($object),
        ];
    }

    /**
     * Reads the posted items of the sky object input.
     *
     * @param string[] $items
     * @param int $limit the rest of the items is left out
     * @return array designations (or null) indexed by object id in the order of the items, unknown objects are left out
     */
    public static function findItems(array $items, $limit = self::ITEM_LIMIT)
    {
        $designations = [];

        foreach (array_slice($items, 0, $limit) as $item) {
            list($id, $designation) = array_pad(explode(self::ITEM_SEPARATOR, (string)$item, 2), 2, null);

            // the column is a 32 bit integer
            if (ctype_digit($id) && strlen($id) < 10) {
                $designations[(int)$id] = $designation;
            }
        }

        $objects = self::find()->select(['name', 'id'])->where(['id' => array_keys($designations)])->indexBy('id')->column();
        $aliases = self::findByAliases(array_values(array_filter($designations)));
        $result = [];

        foreach (array_intersect_key($designations, $objects) as $id => $designation) {
            $isKnown = $designation !== null && in_array($id, array_column($aliases[$designation] ?? [], 'id'));
            $result[$id] = $isKnown ? self::getOtherDesignation($designation, $objects[$id]) : null;
        }

        return $result;
    }

    /**
     * Finds objects by the beginning of a catalog number or by a part of a nickname.
     *
     * @param string $text
     * @return array list of objects (as arrays)
     */
    public static function search($text)
    {
        $alias = SkyMatcher::designationKey($text);
        $pattern = $alias . '%';

        if ($alias === null) {
            $alias = SkyMatcher::nicknameKey($text);
            $pattern = '%' . $alias . '%';
        }

        if ($alias === '') {
            return [];
        }

        $aliases = (new Query())
            ->select('alias')
            ->distinct()
            ->from('sky_object_aliases')
            ->where(['like', 'alias', $pattern, false])
            ->limit(200)
            ->column();

        // the closest first
        usort($aliases, function ($a, $b) {
            return strlen($a) - strlen($b) ?: strcmp($a, $b);
        });

        $found = self::findByAliases($aliases);
        $objects = [];

        foreach ($aliases as $key) {
            foreach ($found[$key] as $object) {
                $objects += [$object['id'] => $object];
            }
        }

        return array_slice(array_values($objects), 0, self::SEARCH_LIMIT);
    }

    /**
     * NGC 2244 is NGC 2239 in the catalog, but everyone knows it as the former.
     *
     * @param string $alias
     * @param string $name
     * @return string|null
     */
    private static function getOtherDesignation($alias, $name)
    {
        return $alias !== $name && preg_match('~^(NGC|IC) ~', $alias) ? $alias : null;
    }
}
