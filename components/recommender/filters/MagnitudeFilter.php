<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use app\components\recommender\Filter;
use yii\db\Query;

/**
 * Objects between two magnitudes. The ones without a known magnitude are kept, the card says so.
 * At the ends of the scale there is no limit.
 */
class MagnitudeFilter extends Filter
{
    const SCALE_MIN = -2;
    const SCALE_MAX = 18;
    const STEP = 0.5;

    public function getKey()
    {
        return 'fenyesseg';
    }

    public function getLabel()
    {
        return 'Fényesség (magnitúdó)';
    }

    public function getDefault(Context $context)
    {
        // guests get Messier and Caldwell objects only, the faintest of them (about 10–13 mag) are fine too
        return ['min' => self::SCALE_MIN, 'max' => $context->isGuest() ? self::SCALE_MAX : 12];
    }

    public function parse($input, Context $context)
    {
        $default = $this->getDefault($context);

        if (!is_array($input)) {
            return $default;
        }

        $min = self::parseMagnitude($input['min'] ?? null) ?? $default['min'];
        $max = self::parseMagnitude($input['max'] ?? null) ?? $default['max'];

        return ['min' => min($min, $max), 'max' => max($min, $max)];
    }

    public function apply(Query $query, $value, Context $context)
    {
        if ($value['min'] > self::SCALE_MIN) {
            $query->andWhere('o.magnitude IS NULL OR o.magnitude >= ' . $this->param('min'), [$this->param('min') => $value['min']]);
        }

        if ($value['max'] < self::SCALE_MAX) {
            $query->andWhere('o.magnitude IS NULL OR o.magnitude <= ' . $this->param('max'), [$this->param('max') => $value['max']]);
        }
    }

    /**
     * @return float|null
     */
    private static function parseMagnitude($input)
    {
        if (!is_scalar($input) || !is_numeric($input)) {
            return null;
        }

        $value = round((float)$input / self::STEP) * self::STEP;

        return max(self::SCALE_MIN, min(self::SCALE_MAX, $value));
    }
}
