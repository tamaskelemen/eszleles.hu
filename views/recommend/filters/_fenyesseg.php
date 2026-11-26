<?php

use app\components\recommender\filters\MagnitudeFilter;
use yii\helpers\Html;

/* @var $filter MagnitudeFilter */
/* @var $value array min, max */

$attributes = [
    'min' => MagnitudeFilter::SCALE_MIN,
    'max' => MagnitudeFilter::SCALE_MAX,
    'step' => MagnitudeFilter::STEP,
];
?>
<fieldset class="rec-range" data-min="<?= MagnitudeFilter::SCALE_MIN ?>" data-max="<?= MagnitudeFilter::SCALE_MAX ?>">
    <legend class="rec-filter__label">
        <?= Html::encode($filter->getLabel()) ?>
        <output class="rec-range__output"></output>
    </legend>
    <div class="rec-range__track">
        <span class="rec-range__fill" aria-hidden="true"></span>
        <!-- the limit of the equipment will be marked here -->
        <span class="rec-range__mark" hidden></span>
        <?= Html::input('range', $filter->getInputName() . '[min]', $value['min'], $attributes + ['aria-label' => 'Legfényesebb (alsó határ)', 'class' => 'rec-range__input rec-range__input--min']) ?>
        <?= Html::input('range', $filter->getInputName() . '[max]', $value['max'], $attributes + ['aria-label' => 'Leghalványabb (felső határ)', 'class' => 'rec-range__input rec-range__input--max']) ?>
    </div>
    <div class="rec-range__scale" aria-hidden="true"><span>fényes</span><span>halvány</span></div>
    <p class="rec-filter__hint">A fényességadat nélküli objektumok is jöhetnek.</p>
</fieldset>
