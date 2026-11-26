<?php

namespace app\components\recommender;

use app\components\recommender\filters\AltitudeFilter;
use app\components\recommender\filters\CatalogFilter;
use app\components\recommender\filters\HighFilter;
use app\components\recommender\filters\MagnitudeFilter;
use app\components\recommender\filters\MoonFilter;
use app\components\recommender\filters\PopularFilter;
use app\components\recommender\filters\ProgramFilter;
use app\components\recommender\filters\SizeFilter;
use app\components\recommender\filters\TypeFilter;
use app\components\recommender\filters\UnobservedFilter;
use app\models\SkyObject;
use yii\db\Expression;
use yii\db\Query;

/**
 * Recommends a deep sky object to observe now: a random one of the objects that pass every filter, the filters with a
 * weight make some objects more likely.
 */
class Recommender
{
    /** The filters in the order they are shown */
    const FILTERS = [
        ProgramFilter::class,
        UnobservedFilter::class,
        CatalogFilter::class,
        TypeFilter::class,
        MagnitudeFilter::class,
        SizeFilter::class,
        AltitudeFilter::class,
        MoonFilter::class,
        HighFilter::class,
        PopularFilter::class,
    ];

    /** @var Context */
    public $context;

    /** @var Filter[] indexed by key */
    private $filters = [];

    public function __construct(Context $context)
    {
        $this->context = $context;

        foreach (self::FILTERS as $class) {
            /** @var Filter $filter */
            $filter = new $class();
            $this->filters[$filter->getKey()] = $filter;
        }
    }

    /**
     * @return Filter[] indexed by key
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param array $input values from the request indexed by key, guests get the defaults
     * @return array valid values indexed by key
     */
    public function parseValues(array $input)
    {
        $values = [];

        foreach ($this->filters as $key => $filter) {
            $values[$key] = $this->context->isGuest() ? $filter->getDefault($this->context) : $filter->parse($input[$key] ?? null, $this->context);
        }

        return $values;
    }

    /**
     * @param array $values from parseValues()
     * @param int|null $currentId the object being shown, it is kept if it still passes the filters
     * @param int[] $excludeIds recently shown objects, they are avoided if there is any other
     * @return SkyObject|null null if no object passes the filters
     */
    public function recommend(array $values, $currentId = null, array $excludeIds = [])
    {
        if ($currentId !== null) {
            $current = $this->buildQuery($values)->andWhere(['o.id' => $currentId])->one();

            if ($current !== null) {
                return $current;
            }
        }

        $excludeIds = array_merge($excludeIds, $currentId === null ? [] : [$currentId]);
        $weight = $this->buildWeight($values);
        // weighted random choice in one pass (Efraimidis–Spirakis)
        $order = new Expression("-ln(1 - random()) / ($weight)");

        if (!empty($excludeIds)) {
            $object = $this->buildQuery($values)->andWhere(['not in', 'o.id', $excludeIds])->orderBy($order)->one();

            if ($object !== null) {
                return $object;
            }
        }

        return $this->buildQuery($values)->orderBy($order)->one();
    }

    /**
     * @param array $values from parseValues()
     * @return int number of objects passing the filters
     */
    public function count(array $values)
    {
        return (int)$this->buildQuery($values)->count();
    }

    /**
     * @param array $values
     * @return \yii\db\ActiveQuery
     */
    private function buildQuery(array $values)
    {
        $query = SkyObject::find()
            ->alias('o')
            ->where(['not in', 'o.type', SkyObject::NON_DEEP_SKY_TYPES])
            // the objects below the horizon, without the trigonometry; dec > lat − 90
            ->andWhere(['>', 'o.dec', $this->context->latitude - 90]);

        foreach ($this->filters as $key => $filter) {
            $filter->apply($query, $values[$key], $this->context);
        }

        return $query;
    }

    /**
     * @param array $values
     * @return string SQL expression
     */
    private function buildWeight(array $values)
    {
        $weights = [];

        foreach ($this->filters as $key => $filter) {
            $weight = $filter->getWeight($values[$key], $this->context);

            if ($weight !== null) {
                $weights[] = $weight;
            }
        }

        return empty($weights) ? '1' : implode(' * ', $weights);
    }
}
