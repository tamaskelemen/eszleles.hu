<?php

namespace app\components\recommender;

use yii\db\Query;

/**
 * A filter of the recommender. To add one, write a subclass, a view for its control in views/recommend/filters, and
 * list it in Recommender::FILTERS. Removing it from that list removes it everywhere.
 *
 * Guests always get getDefault(), they see the control disabled.
 */
abstract class Filter
{
    /**
     * @return string the name of the value in the request, also the name of the view (views/recommend/filters/_<key>)
     */
    abstract public function getKey();

    /**
     * @return string
     */
    abstract public function getLabel();

    /**
     * @param Context $context
     * @return mixed the value if the visitor did not choose one
     */
    abstract public function getDefault(Context $context);

    /**
     * @param mixed $input from the request, it can be anything
     * @param Context $context
     * @return mixed a valid value, the default one if the input is not valid
     */
    abstract public function parse($input, Context $context);

    /**
     * Narrows the objects, the table is aliased "o".
     *
     * @param Query $query
     * @param mixed $value
     * @param Context $context
     */
    abstract public function apply(Query $query, $value, Context $context);

    /**
     * @param mixed $value
     * @param Context $context
     * @return string|null SQL expression (positive number) multiplying the chance of an object, null if the filter does
     * not prefer any
     */
    public function getWeight($value, Context $context)
    {
        return null;
    }

    /**
     * @param Context $context
     * @return array extra parameters of the view
     */
    public function getViewParams(Context $context)
    {
        return [];
    }

    /**
     * @return string name of the parameter in the request
     */
    public function getInputName()
    {
        return 'f[' . $this->getKey() . ']';
    }

    /**
     * Unique name of a query parameter.
     *
     * @param string $name
     * @return string
     */
    protected function param($name)
    {
        return ':' . preg_replace('~\W~', '_', $this->getKey()) . '_' . $name;
    }

    /**
     * @param mixed $input
     * @param array $allowed
     * @return array the allowed items of the input, in the order of the allowed ones
     */
    protected static function parseList($input, array $allowed)
    {
        $input = array_map('strval', array_filter((array)$input, 'is_scalar'));

        return array_values(array_intersect($allowed, $input));
    }

    /**
     * @param mixed $input
     * @return bool|null null if it is not given
     */
    protected static function parseBool($input)
    {
        if (!is_scalar($input) || $input === '') {
            return null;
        }

        return in_array((string)$input, ['1', 'true', 'on'], true);
    }
}
