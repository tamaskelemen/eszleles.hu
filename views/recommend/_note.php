<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $object app\models\SkyObject */
/* @var $defaults array telescope, location, date, previous (lists of earlier values) */

$id = 'rec-note-' . $object->id;
$tips = "Tippek az észlelés leírásához:\n"
    . "– Milyen nagyítással nézted?\n"
    . "– Milyen alakja van?\n"
    . "– Mennyire kontrasztos a széle? És a közepe?\n"
    . "– Mennyire homogén?\n"
    . "– Látsz benne valami jellegzetes alakot, halványabb vagy élesebb részeket?";
$field = function ($name, $label, $type = 'text') use ($id, $defaults) {
    $input = Html::input($type, $name, $defaults[$name], [
        'id' => $id . '-' . $name,
        'class' => 'form-control form-control-sm',
        'required' => true,
    ] + ($type === 'date' ? ['max' => date('Y-m-d')] : ['maxlength' => 255, 'list' => $id . '-' . $name . '-list']));
    $list = $type === 'date' ? '' : Html::tag('datalist', implode('', array_map(function ($value) {
        return Html::tag('option', '', ['value' => $value]);
    }, $defaults['previous'][$name] ?? [])), ['id' => $id . '-' . $name . '-list']);

    return Html::tag('div', Html::label($label, $id . '-' . $name) . $input . $list, ['class' => 'rec-note__field']);
};
?>
<div class="rec-note">
    <button class="btn btn-outline btn-sm rec-note__open" type="button" aria-expanded="false" aria-controls="<?= $id ?>">
        <i class="fa fa-pencil" aria-hidden="true"></i> Észlelés feljegyzése
    </button>

    <form class="rec-note__form" id="<?= $id ?>" action="<?= Url::to(['/recommend/observe']) ?>" method="post" hidden>
        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken()) ?>
        <?= Html::hiddenInput('skyObjectId', $object->id) ?>

        <div class="rec-note__head">
            <label for="<?= $id ?>-description">Mit láttál?</label>
            <?= $this->render('//_common-items/_infoTip', ['label' => 'Tippek az észlelés leírásához', 'text' => $tips, 'isMultiline' => true]) ?>
        </div>
        <textarea class="form-control" id="<?= $id ?>-description" name="description" rows="5" required></textarea>

        <div class="rec-note__fields">
            <?= $field('telescope', 'Optika') ?>
            <?= $field('location', 'Helyszín') ?>
            <?= $field('date', 'Időpont', 'date') ?>
        </div>

        <p class="rec-note__error" role="alert" hidden></p>

        <div class="rec-note__actions">
            <button class="btn btn-primary btn-sm" type="submit">Mentés</button>
            <button class="btn btn-link btn-sm rec-note__cancel" type="button">Mégse</button>
        </div>
    </form>

    <div class="rec-note__done" role="status" hidden>
        <p><i class="fa fa-check" aria-hidden="true"></i> Elmentettük az észlelést. <a class="rec-note__link" href="#">Megnézem</a></p>
        <button class="btn btn-primary btn-sm rec-note__next" type="button">
            <i class="fa fa-arrow-right" aria-hidden="true"></i> Új észlelés
        </button>
    </div>
</div>
