<?php
namespace app\commands;

use app\components\SkyLinker;
use app\components\SkyMap;
use app\components\SkyMatcher;
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
    const EXTRA_FILES = ['sharpless.csv', 'barnard.csv', 'ldn.csv', 'lbn.csv', 'vdb.csv'];
    const ALIAS_FILE = 'aliases.csv';

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

            SkyObject::updateAll(['common_name' => null]);
            foreach ($commonNames as $id => $commonName) {
                SkyObject::updateAll(['common_name' => $commonName], ['id' => $id]);
            }

            $transaction->commit();
            SkyMap::clearCache();
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
                'size' => $row['MajAx'] === '' ? null : (float)$row['MajAx'],
            ])->execute();
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
                'size' => $row['size'] === '' ? null : (float)$row['size'],
            ])->execute();
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
