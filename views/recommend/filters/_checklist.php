<?php

use yii\helpers\Html;

/* @var $filter app\components\recommender\Filter */
/* @var $value string[] */
/* @var $items array label (and optional title) indexed by value */
/* @var $hint string|null */
?>
<fieldset>
    <legend class="rec-filter__label"><?= Html::encode($filter->getLabel()) ?></legend>
    <!-- sent even if none is checked, so the defaults do not come back -->
    <input type="hidden" name="<?= $filter->getInputName() ?>[]" value="">
    <div class="rec-chips">
        <?php foreach ($items as $item => $label) { ?>
            <label class="rec-chip" <?= is_array($label) && $label['title'] ? 'title="' . Html::encode($label['title']) . '"' : '' ?>>
                <input type="checkbox" name="<?= $filter->getInputName() ?>[]" value="<?= Html::encode($item) ?>" <?= in_array($item, $value, true) ? 'checked' : '' ?>>
                <span><?= Html::encode(is_array($label) ? $label['label'] : $label) ?></span>
            </label>
        <?php } ?>
    </div>
    <?php if (!empty($hint)) { ?>
        <p class="rec-filter__hint"><?= Html::encode($hint) ?></p>
    <?php } ?>
</fieldset>
