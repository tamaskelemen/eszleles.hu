<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use app\components\recommender\Filter;
use app\models\SkyObject;
use Yii;
use yii\db\Query;

/**
 * Objects of any of the chosen catalogs. Every catalog of the database is listed, so an imported one shows up by
 * itself.
 */
class CatalogFilter extends Filter
{
    const CACHE_KEY = 'recommender-catalogs';
    const CACHE_DURATION = 3600;

    /** Catalogs with fewer objects are left out of the list, they are not catalogs to choose from */
    const MIN_COUNT = 10;

    const GUEST_CATALOGS = ['M', 'C'];
    const DEFAULT_CATALOGS = ['M', 'C', 'NGC', 'IC'];

    /** The ones that are not obvious from the abbreviation */
    const DESCRIPTIONS = [
        'M' => 'Messier',
        'C' => 'Caldwell',
        'B' => 'Barnard',
        'SH2' => 'Sharpless',
        'VDB' => 'van den Bergh',
        'PAL' => 'Palomar',
        'ABELL' => 'Abell planetáris ködök',
        'ACO' => 'Abell galaxishalmazok',
        'LDN' => 'Lynds sötét ködök',
        'LBN' => 'Lynds fényes ködök',
    ];

    public function getKey()
    {
        return 'katalogus';
    }

    public function getLabel()
    {
        return 'Katalógusok';
    }

    public function getDefault(Context $context)
    {
        return array_values(array_intersect(array_keys(self::getCatalogs()), $context->isGuest() ? self::GUEST_CATALOGS : self::DEFAULT_CATALOGS));
    }

    public function parse($input, Context $context)
    {
        return $input === null ? $this->getDefault($context) : self::parseList($input, array_keys(self::getCatalogs()));
    }

    public function apply(Query $query, $value, Context $context)
    {
        // none is chosen: any of them
        if (!empty($value)) {
            $query->andWhere('o.catalogs && ' . $this->param('list') . '::text[]', [$this->param('list') => '{' . implode(',', $value) . '}']);
        }
    }

    public function getViewParams(Context $context)
    {
        return ['catalogs' => self::getCatalogs()];
    }

    /**
     * @return array catalogs indexed by prefix: label, description, count; in the order of SkyObject::DESIGNATION_FORMATS
     */
    public static function getCatalogs()
    {
        return Yii::$app->cache->getOrSet(self::CACHE_KEY, function () {
            $counts = (new Query())
                ->select(['count' => 'COUNT(*)', 'catalog'])
                ->from(['c' => (new Query())->select(['catalog' => 'unnest(catalogs)'])->from('sky_objects')->where(['not in', 'type', SkyObject::NON_DEEP_SKY_TYPES])])
                ->groupBy('catalog')
                ->having(['>=', 'COUNT(*)', self::MIN_COUNT])
                ->indexBy('catalog')
                ->column();
            $order = array_flip(array_keys(SkyObject::DESIGNATION_FORMATS));

            uksort($counts, function ($a, $b) use ($order) {
                return ($order[$a] ?? PHP_INT_MAX) <=> ($order[$b] ?? PHP_INT_MAX) ?: strcmp($a, $b);
            });

            $catalogs = [];

            foreach ($counts as $catalog => $count) {
                $catalogs[$catalog] = [
                    'label' => SkyObject::formatCatalog($catalog),
                    'description' => self::DESCRIPTIONS[$catalog] ?? null,
                    'count' => (int)$count,
                ];
            }

            return $catalogs;
        }, self::CACHE_DURATION);
    }

    public static function clearCache()
    {
        Yii::$app->cache->delete(self::CACHE_KEY);
    }
}
