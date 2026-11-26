<?php

use app\models\ObservingPlan;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\ObservingPlanForm */

$this->registerJsFile('/js/sky-objects.js?v=' . filemtime(Yii::getAlias('@webroot/js/sky-objects.js')), [
    'depends' => [JqueryAsset::class],
]);
$plan = $model->getPlan();
?>
<div class="plan-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 100, 'placeholder' => 'pl. Téli galaxisok']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'is_public')->checkbox()
        ->hint('A nyilvános tervet mások is láthatják az Észlelőprogramok oldalán, de az előrehaladásodat ott is csak te látod.') ?>

    <?php /* the sky object input of the observations, without following a title, see sky-objects.js */ ?>
    <?= Html::beginTag('div', [
        'id' => 'plan-sky-objects',
        'class' => 'sky-objects form-group',
        'hidden' => true,
        'data-items' => Json::encode($model->getItems()),
        'data-dismissed' => '[]',
        'data-items-name' => Html::getInputName($model, 'skyObjectItems') . '[]',
        'data-title-name' => Html::getInputName($model, 'skyObjectTitle'),
        'data-title' => '',
        'data-search-url' => Url::to(['/sky/search']),
    ]) ?>
        <label for="plan-sky-objects-search">Objektumok</label>
        <ul class="sky-objects__list" aria-live="polite"></ul>

        <div class="sky-objects__search">
            <input type="text" id="plan-sky-objects-search" class="form-control" autocomplete="off" maxlength="100"
                   placeholder="Objektum hozzáadása, pl. M 42 vagy Rozetta-köd"
            >
            <div class="dropdown-menu"></div>
        </div>
        <small class="form-text text-muted">Legfeljebb <?= ObservingPlan::ITEM_LIMIT ?> objektum, a hozzáadás sorrendjében.</small>
        <?= Html::error($model, 'skyObjectItems', ['class' => 'invalid-feedback d-block']) ?>
    <?= Html::endTag('div') ?>

    <noscript>
        <p class="text-muted">Az objektumok hozzáadásához JavaScript szükséges.</p>
    </noscript>

    <div class="form-group">
        <?= Html::submitButton('Mentés', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Mégse', $plan->isNewRecord ? ['/program/index'] : ['view', 'id' => $plan->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
