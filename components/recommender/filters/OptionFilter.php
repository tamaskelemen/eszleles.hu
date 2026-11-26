<?php

namespace app\components\recommender\filters;

use app\components\recommender\Context;
use app\components\recommender\Filter;

/**
 * A filter with a number chosen from a list.
 */
abstract class OptionFilter extends Filter
{
    /**
     * @return array labels indexed by value
     */
    abstract public function getOptions();

    /**
     * {@inheritdoc}
     */
    public function parse($input, Context $context)
    {
        foreach (array_keys($this->getOptions()) as $value) {
            if (is_scalar($input) && (string)$input === (string)$value) {
                return $value;
            }
        }

        return $this->getDefault($context);
    }
}
