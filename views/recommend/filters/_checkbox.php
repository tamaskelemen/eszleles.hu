<?php

use yii\helpers\Html;

/* @var $filter app\components\recommender\Filter */
/* @var $value bool */
/* @var $hint string|null */
/* @var $info string|null explanation behind an info icon */

$id = 'rec-' . $filter->getKey();
?>
<input type="hidden" name="<?= $filter->getInputName() ?>" value="0">
<div class="rec-switch-row">
    <label class="rec-switch" for="<?= $id ?>">
        <input type="checkbox" id="<?= $id ?>" name="<?= $filter->getInputName() ?>" value="1" <?= $value ? 'checked' : '' ?>>
        <span class="rec-switch__track" aria-hidden="true"></span>
        <?= Html::encode($filter->getLabel()) ?>
    </label>
    <?php if (!empty($info)) { ?>
        <?= $this->render('//_common-items/_infoTip', ['label' => 'Mit jelent?', 'text' => $info]) ?>
    <?php } ?>
</div>
<?php if (!empty($hint)) { ?>
    <p class="rec-filter__hint"><?= Html::encode($hint) ?></p>
<?php } ?>
