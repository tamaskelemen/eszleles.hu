<?php
namespace app\commands;

use app\components\BadgeSync;
use app\components\recommender\filters\CatalogFilter;
use app\components\SkyLinker;
use app\components\SkyMap;
use app\components\SkyMatcher;
use app\components\SkyPrograms;
use app\models\Image;
use app\models\ImageTag;
use app\models\ObservationSkyObject;
use app\models\Observe;
use app\models\SkyObject;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Query;

class SkyController extends Controller
{
    const CATALOG_FILES = ['NGC.csv', 'addendum.csv'];
    /** Catalogs in our own format, see the README of seed/sky */
    const EXTRA_FILES = ['sharpless.csv', 'barnard.csv', 'ldn.csv', 'lbn.csv', 'vdb.csv', 'palomar.csv', 'abell.csv', 'aco.csv'];
    const ALIAS_FILE = 'aliases.csv';
    const LIST_DIRECTORY = 'lists/';

    /** Identifiers of OpenNGC we use => how the matcher knows the catalog */
    const IDENTIFIER_PREFIXES = [
        'C' => 'Caldwell',
        'Cl' => 'Collinder',
        'Mel' => 'Melotte',
        'SH' => 'Sh',
        'LBN' => 'LBN',
        'UGC' => 'UGC',
        'PGC' => 'PGC',
    ];

    public $path = __DIR__ . '/../seed/sky/';

    /** @var string csv file with object_name, constellation and count columns, used instead of the database */
    public $input;

    /** @var bool whether to store the objects, instead of a dry run */
    public $save = false;

    /** @var string */
    public $output = __DIR__ . '/../runtime/sky-match.csv';

    /**
     * {@inheritdoc}
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            $actionID === 'match' ? ['input', 'output', 'save'] : []
        );
    }

    /**
     * Imports the catalog of the sky map. It can be run again any time, the ids of the objects do not change.
     */
    public function actionImport()
    {
        $rows = $this->readCatalog();
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $extraRows = $this->readExtraCatalogs();
            $this->importObjects($rows);
            $this->importExtraObjects($extraRows);

            $ids = SkyObject::find()->select('id')->indexBy('name')->column();
            $aliases = array_merge($this->collectCatalogAliases($rows, $ids), $this->collectExtraAliases($extraRows, $ids));
            list($fileAliases, $commonNames) = $this->readAliases($ids);

            foreach ($fileAliases as $alias => $objectIds) {
                // M 102
                if (preg_match('~^M (\d+)$~', $alias, $match)) {
                    SkyObject::updateAll(['messier' => (int)$match[1]], ['id' => $objectIds]);
                }
            }

            // our own aliases win
            $aliases = array_merge($aliases, $fileAliases);

            $this->saveAliases($aliases);
            $this->importLists($aliases);
            $this->saveCatalogs();

            SkyObject::updateAll(['common_name' => null]);
            foreach ($commonNames as $id => $commonName) {
                SkyObject::updateAll(['common_name' => $commonName], ['id' => $id]);
            }

            $transaction->commit();
            SkyMap::clearCache();
            CatalogFilter::clearCache();

            // the lists may have changed
            foreach (Observe::find()->select('observer_id')->distinct()->column() as $userId) {
                BadgeSync::markChanged($userId);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $this->stdout(count($ids) . " objects, " . count($aliases) . " aliases imported.\n");

        return ExitCode::OK;
    }

    /**
     * Finds the objects by the titles of the deep sky observations. Without --save it only writes a report of them.
     * What the uploaders and the admins have decided does not change.
     */
    public function actionMatch()
    {
        if (!SkyObject::find()->exists()) {
            $this->stderr("The catalog is empty, run sky/import first.\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($this->save) {
            return $this->saveMatches();
        }

        $matcher = new SkyMatcher([SkyObject::class, 'findByAliases']);
        $titles = $this->input ? $this->readTitles($this->input) : $this->findTitles();

        $lines = [SkyMatcher::STATUS_REVIEW => [], SkyMatcher::STATUS_NONE => [], SkyMatcher::STATUS_AUTO => []];
        $counts = array_fill_keys(array_keys($lines), 0);

        foreach ($titles as $title) {
            $match = $matcher->match($title['object_name'], $title['constellation']);
            $counts[$match['status']] += $title['count'];

            $lines[$match['status']][] = [
                $match['status'],
                $title['object_name'],
                $title['constellation'],
                implode(', ', array_map([SkyObject::class, 'buildLabel'], $match['objects'])),
                implode(', ', $match['unknown']),
                implode(', ', $match['reasons']),
                $title['count'],
            ];
        }

        $handle = fopen($this->output, 'w');

        if ($handle === false) {
            $this->stderr("Can not write {$this->output}\n");
            return ExitCode::CANTCREAT;
        }

        fwrite($handle, "\xEF\xBB\xBF"); // for Excel
        fputcsv($handle, ['állapot', 'cím', 'csillagkép', 'felismert objektumok', 'ismeretlen katalógusszám', 'megjegyzés', 'darab'], ';', '"', '');

        foreach (array_merge(...array_values($lines)) as $line) {
            fputcsv($handle, $line, ';', '"', '');
        }

        fclose($handle);

        $total = max(1, array_sum($counts));
        foreach ($counts as $status => $count) {
            $this->stdout(sprintf("%-7s %5d observations (%.1f%%), %d titles\n", $status, $count, 100 * $count / $total, count($lines[$status])));
        }
        $this->stdout("Nothing was saved, the report is here: {$this->output}\n");

        return ExitCode::OK;
    }

    private function saveMatches()
    {
        if ($this->input) {
            $this->stderr("--input can not be saved.\n");
            return ExitCode::USAGE;
        }

        $linker = new SkyLinker();
        $count = 0;

        foreach (Observe::find()->where(['type' => Observe::TYPE_DEEP_SKY])->each() as $observe) {
            $linker->rematch($observe);
            $count++;
        }

        // the other types are matched by their tags only
        $tagged = Image::find()->select('observe_id')->where(['id' => ImageTag::find()->select('image_id')]);

        foreach (Observe::find()->where(['id' => $tagged])->andWhere(['!=', 'type', Observe::TYPE_DEEP_SKY])->each() as $observe) {
            $linker->syncTags($observe);
            $count++;
        }

        $this->stdout("$count observations matched.\n");

        $statuses = ObservationSkyObject::find()->select(['status', 'count' => 'COUNT(DISTINCT observation_id)'])->groupBy('status')->asArray()->all();

        foreach ($statuses as $row) {
            $this->stdout(sprintf("%-9s %5d observations\n", $row['status'], $row['count']));
        }

        return ExitCode::OK;
    }

    private function readCatalog()
    {
        $rows = [];

        foreach (self::CATALOG_FILES as $file) {
            foreach ($this->readCsv($this->path . $file, ';') as $row) {
                $row['name'] = self::formatName($row['Name']);

                // components of galaxy pairs (NED01) and objects that do not exist are left out
                if ($row['name'] !== null && $row['RA'] !== '' && $row['Type'] !== 'NonEx') {
                    $rows[$row['name']] = $row;
                }
            }
        }

        return $rows;
    }

    private function readExtraCatalogs()
    {
        $rows = [];

        foreach (self::EXTRA_FILES as $file) {
            $rows = array_merge($rows, $this->readCsv($this->path . $file, ';'));
        }

        return $rows;
    }

    private function importObjects(array $rows)
    {
        foreach ($rows as $name => $row) {
            if ($row['Type'] === 'Dup') {
                continue;
            }

            Yii::$app->db->createCommand()->upsert('sky_objects', [
                'name' => $name,
                'messier' => $row['M'] === '' ? null : (int)$row['M'],
                'type' => $row['Type'],
                // the two parts of Serpens are Se1 and Se2
                'constellation' => preg_replace('~^Se\d$~', 'Ser', $row['Const']),
                'ra' => 15 * self::parseSexagesimal($row['RA']),
                'dec' => self::parseSexagesimal($row['Dec']),
                'size' => self::parseNumber($row['MajAx']),
            ] + self::brightnessColumns(
                // the V magnitude is closer to what the eye sees
                self::parseNumber($row['V-Mag']) ?? self::parseNumber($row['B-Mag']),
                self::parseNumber($row['SurfBr']),
                self::parseNumber($row['MajAx']),
                self::parseNumber($row['MinAx'])
            ))->execute();
        }
    }

    private function importExtraObjects(array $rows)
    {
        foreach ($rows as $row) {
            if ($row['same_as'] !== '') {
                continue;
            }

            Yii::$app->db->createCommand()->upsert('sky_objects', [
                'name' => $row['name'],
                'type' => $row['type'],
                'constellation' => $row['constellation'],
                'ra' => (float)$row['ra'],
                'dec' => (float)$row['dec'],
                'size' => self::parseNumber($row['size']),
            ] + self::brightnessColumns(
                // optional columns
                self::parseNumber($row['magnitude'] ?? ''),
                self::parseNumber($row['surface_brightness'] ?? ''),
                self::parseNumber($row['size']),
                self::parseNumber($row['min_axis'] ?? '')
            ))->execute();
        }
    }

    /**
     * @return array lists of object ids indexed by alias
     */
    private function collectExtraAliases(array $rows, array $ids)
    {
        $aliases = [];

        foreach ($rows as $row) {
            $key = SkyMatcher::designationKey($row['name']);

            if ($key === null) {
                throw new \Exception("The matcher does not know this catalog: {$row['name']}");
            }

            $aliases[$key] = $this->findIds($row['same_as'] ?: $row['name'], $ids, $row['name']);

            // optional column, other designations of the object separated by a comma
            foreach (array_filter(explode(',', $row['aliases'] ?? '')) as $designation) {
                $aliasKey = SkyMatcher::designationKey($designation);

                if ($aliasKey === null) {
                    throw new \Exception("The matcher does not know this catalog: $designation ({$row['name']})");
                }

                $aliases[$aliasKey] = $aliases[$key];
            }
        }

        return $aliases;
    }

    /**
     * @param string $names names of objects separated by a comma
     * @param array $ids
     * @param string $source what is being imported
     * @return int[]
     * @throws \Exception if an object does not exist
     */
    private function findIds($names, array $ids, $source)
    {
        return array_map(function ($name) use ($ids, $source) {
            if (!isset($ids[$name])) {
                throw new \Exception("Unknown object: $name ($source)");
            }

            return $ids[$name];
        }, explode(',', $names));
    }

    /**
     * @return array lists of object ids indexed by alias
     */
    private function collectCatalogAliases(array $rows, array $ids)
    {
        $aliases = [];
        $names = [];

        foreach ($rows as $name => $row) {
            $target = self::resolveDuplicate($name, $rows);

            if ($target === null || !isset($ids[$target])) {
                continue;
            }

            $designations = [$name];

            if ($target === $name) {
                if ($row['M'] !== '') {
                    $designations[] = 'M ' . (int)$row['M'];
                }

                foreach (explode(',', $row['Identifiers']) as $identifier) {
                    $designations[] = self::formatIdentifier($identifier);
                }

                foreach (explode(',', $row['Common names']) as $commonName) {
                    $key = SkyMatcher::nicknameKey(preg_replace('~^the ~', '', trim($commonName)));

                    if (strlen($key) >= SkyMatcher::NICKNAME_MIN_LENGTH) {
                        $names[$key][$ids[$target]] = $ids[$target];
                    }
                }
            }

            foreach (array_filter($designations) as $designation) {
                $key = SkyMatcher::designationKey($designation);

                // a catalog number belongs to one object, the name of an object is stronger than its identifiers
                if ($key !== null && ($designation === $name || !isset($aliases[$key]))) {
                    $aliases[$key] = [$ids[$target]];
                }
            }
        }

        return $aliases + array_map('array_values', $names);
    }

    /**
     * @return array lists of object ids indexed by alias, and nicknames to display indexed by object id
     */
    private function readAliases(array $ids)
    {
        $aliases = [];
        $commonNames = [];

        foreach ($this->readCsv($this->path . self::ALIAS_FILE, ';') as $row) {
            $designation = SkyMatcher::designationKey($row['alias']);
            $objectIds = $this->findIds($row['objects'], $ids, $row['alias']);

            $aliases[$designation ?? SkyMatcher::nicknameKey($row['alias'])] = $objectIds;

            if ($designation === null && count($objectIds) === 1) {
                $commonNames += [$objectIds[0] => $row['alias']];
            }
        }

        return [$aliases, $commonNames];
    }

    private function saveAliases(array $aliases)
    {
        $rows = [];

        foreach ($aliases as $alias => $objectIds) {
            foreach ($objectIds as $objectId) {
                $rows[] = [$objectId, $alias];
            }
        }

        Yii::$app->db->createCommand()->delete('sky_object_aliases')->execute();

        foreach (array_chunk($rows, 1000) as $chunk) {
            Yii::$app->db->createCommand()->batchInsert('sky_object_aliases', ['sky_object_id', 'alias'], $chunk)->execute();
        }
    }

    /**
     * Items of the observing programs.
     *
     * @param array $aliases lists of object ids indexed by alias
     * @throws \Exception if a list is not complete
     */
    private function importLists(array $aliases)
    {
        $rows = [];

        foreach (SkyPrograms::ALIAS_PREFIXES as $list => $prefix) {
            $numbers = [];

            foreach ($aliases as $alias => $objectIds) {
                if (preg_match('~^' . $prefix . ' (\d+)$~', $alias, $match)) {
                    $numbers[(int)$match[1]] = $objectIds;
                }
            }

            ksort($numbers);

            // many objects have more NGC numbers, they are listed by the first one
            $isUnique = $list === SkyPrograms::NGC;
            $used = [];

            foreach ($numbers as $number => $objectIds) {
                foreach ($objectIds as $objectId) {
                    if ($isUnique && isset($used[$objectId])) {
                        continue;
                    }

                    $used[$objectId] = true;
                    $rows[] = [$list, $number, $objectId];
                }
            }
        }

        foreach (SkyPrograms::FILES as $list => list($file, $count)) {
            $numbers = [];

            foreach ($this->readCsv($this->path . self::LIST_DIRECTORY . $file, ';') as $row) {
                foreach (explode(',', $row['objects']) as $designation) {
                    $key = SkyMatcher::designationKey($designation);

                    if (!isset($aliases[$key])) {
                        throw new \Exception("Unknown object: $designation ($file)");
                    }

                    foreach ($aliases[$key] as $objectId) {
                        $numbers[(int)$row['number']][$objectId] = [$list, (int)$row['number'], $objectId];
                    }
                }
            }

            if (count($numbers) !== $count) {
                throw new \Exception("$file has " . count($numbers) . " items instead of $count");
            }

            $rows = array_merge($rows, ...array_map('array_values', array_values($numbers)));
        }

        Yii::$app->db->createCommand()->delete('sky_list_items')->execute();

        foreach (array_chunk($rows, 1000) as $chunk) {
            Yii::$app->db->createCommand()->batchInsert('sky_list_items', ['list', 'number', 'sky_object_id'], $chunk)->execute();
        }
    }

    /**
     * The catalogs of an object are the prefixes of its designations ("NGC 1976" => "NGC"). The galaxy clusters above
     * Abell 86 have an "ABELL" alias as well, but they belong to ACO only.
     */
    private function saveCatalogs()
    {
        Yii::$app->db->createCommand(<<<'SQL'
UPDATE sky_objects o SET catalogs = COALESCE((
    SELECT array_agg(DISTINCT split_part(a.alias, ' ', 1) ORDER BY split_part(a.alias, ' ', 1))
    FROM sky_object_aliases a
    WHERE a.sky_object_id = o.id AND a.alias LIKE '% %'
), '{}')
SQL
        )->execute();

        Yii::$app->db->createCommand(<<<'SQL'
UPDATE sky_objects SET catalogs = array_remove(catalogs, 'ABELL')
WHERE 'ACO' = ANY(catalogs) AND name NOT LIKE 'Abell %'
SQL
        )->execute();
    }

    /**
     * @param float|null $magnitude
     * @param float|null $surfaceBrightness given by the catalog, estimated if it is missing
     * @param float|null $majorAxis arc minutes
     * @param float|null $minorAxis arc minutes
     * @return array
     */
    private static function brightnessColumns($magnitude, $surfaceBrightness, $majorAxis, $minorAxis)
    {
        return [
            'magnitude' => $magnitude,
            'surface_brightness' => $surfaceBrightness ?? SkyObject::estimateSurfaceBrightness($magnitude, $majorAxis, $minorAxis),
            'min_axis' => $minorAxis,
        ];
    }

    /**
     * @param string $value
     * @return float|null
     */
    private static function parseNumber($value)
    {
        return is_numeric($value) ? (float)$value : null;
    }

    private function findTitles()
    {
        return (new Query())
            ->select(['object_name', 'constellation', 'count' => 'COUNT(*)'])
            ->from(Observe::tableName())
            ->where(['type' => Observe::TYPE_DEEP_SKY])
            ->groupBy(['object_name', 'constellation'])
            ->orderBy(['count' => SORT_DESC, 'object_name' => SORT_ASC])
            ->all();
    }

    private function readTitles($file)
    {
        return $this->readCsv($file, ',');
    }

    private function readCsv($file, $delimiter)
    {
        $handle = fopen($file, 'r');

        if ($handle === false) {
            throw new \Exception("Can not read $file");
        }

        $header = fgetcsv($handle, 0, $delimiter, '"', '');
        $rows = [];

        while (($data = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            // skip blank lines
            if ($data !== [null]) {
                $rows[] = array_combine($header, $data);
            }
        }

        fclose($handle);

        return $rows;
    }

    /**
     * "NGC0224" => "NGC 224", "ESO056-115" => "ESO 56-115"
     *
     * @return string|null
     */
    private static function formatName($name)
    {
        if (!preg_match('~^([A-Za-z]+?)0*(\d+(?:-\d+)?[A-Z]?)$~', $name, $match)) {
            return null;
        }

        $catalog = $match[1] === 'Cl' ? 'Cr' : $match[1];

        return $catalog . ' ' . $match[2];
    }

    /**
     * @return string|null
     */
    private static function formatIdentifier($identifier)
    {
        if (!preg_match('~^([A-Za-z]+) ([\d-]+)$~', trim($identifier), $match)) {
            return null;
        }

        $catalog = self::IDENTIFIER_PREFIXES[$match[1]] ?? null;

        return $catalog === null ? null : $catalog . ' ' . $match[2];
    }

    /**
     * @return string|null name of the object the row is the duplicate of, or the name itself
     */
    private static function resolveDuplicate($name, array $rows)
    {
        for ($i = 0; $i < 5 && isset($rows[$name]); $i++) {
            $row = $rows[$name];

            if ($row['Type'] !== 'Dup') {
                return $name;
            }

            $next = null;

            foreach (['NGC', 'IC'] as $catalog) {
                if ($row[$catalog] !== '') {
                    $next = self::formatName($catalog . explode(' ', explode(',', $row[$catalog])[0])[0]);
                    break;
                }
            }

            if ($next === null) {
                return null;
            }

            $name = $next;
        }

        return null;
    }

    private static function parseSexagesimal($value)
    {
        $parts = explode(':', ltrim($value, '+-'));
        $result = $parts[0] + $parts[1] / 60 + $parts[2] / 3600;

        return $value[0] === '-' ? -$result : $result;
    }
}
