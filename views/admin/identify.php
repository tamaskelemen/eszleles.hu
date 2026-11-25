<?php

use app\models\Observe;
use app\widgets\SkyObjectInput;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Observe */
/* @var $backUrl string */

$this->title = 'Azonosítás: ' . $model->object_name;
?>
<div class="admin-identify container pt-4">
    <p><?= Html::a('← Vissza a listához', $backUrl) ?></p>

    <h1><?= Html::a(Html::encode($model->object_name), $model->getViewUrl()) ?></h1>

    <p class="hint-block">
        <?= Html::encode(implode(' · ', array_filter([
            Observe::getTypeName($model->type),
            $model->observer ? $model->observer->name : null,
            $model->date,
            $model->constellation,
        ]))) ?>
    </p>

    <div class="row">
        <div class="col-lg-6 col-12">
            <?= Html::beginForm() ?>
            <?= SkyObjectInput::widget(['model' => $model, 'followsTitle' => false]) ?>

            <p class="hint-block">
                Amit itt mentesz, azt az automatikus felismerés nem írja felül. Ha a lista üres, az észlelés
                „nincs objektuma” jelölést kap.
            </p>

            <?= Html::submitButton('Mentés', ['class' => 'btn btn-primary']) ?>
            <?= Html::endForm() ?>
        </div>

        <div class="col-lg-6 col-12">
            <?php if ($model->thumbnail) { ?>
                <?= Html::a(Html::img($model->thumbnail->path, ['alt' => $model->object_name, 'class' => 'img-fluid mb-3']), $model->getViewUrl()) ?>
            <?php } ?>
            <p><?= nl2br(Html::encode($model->description)) ?></p>
        </div>
    </div>
</div>
