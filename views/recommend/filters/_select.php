<?php

use yii\helpers\Html;

/* @var $filter app\components\recommender\filters\OptionFilter|app\components\recommender\Filter */
/* @var $value mixed */
/* @var $options array|null labels indexed by value, the options of the filter if null */
/* @var $hint string|null */

$id = 'rec-' . $filter->getKey();
?>
<label class="rec-filter__label" for="<?= $id ?>"><?= Html::encode($filter->getLabel()) ?></label>
<?= Html::dropDownList($filter->getInputName(), (string)$value, $options ?? $filter->getOptions(), ['class' => 'custom-select custom-select-sm', 'id' => $id]) ?>
<?php if (!empty($hint)) { ?>
    <p class="rec-filter__hint"><?= Html::encode($hint) ?></p>
<?php } ?>
