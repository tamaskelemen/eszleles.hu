<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ObserveSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="observe-search">
    <p>
        <?= Html::button('Bővített keresés', ['class' => 'btn btn-primary', 'data-toggle' => "collapse", 'data-target' => '#search']) ?>
    </p>

    <div class="container collapse" id="search" >
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
        ]); ?>
        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'id') ?>
            </div>

            <div class="col-lg-6">
                <?= $form->field($model, 'catalog_number') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'constellation') ?>
            </div>

            <div class="col-lg-6">
                <?= $form->field($model, 'object_type')->dropDownList(\app\components\Data::listObjectType()) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'telescope') ?>
            </div>

            <div class="col-lg-6">
                <?php  echo $form->field($model, 'seeing') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'transparency') ?>
            </div>

            <div class="col-lg-6">
                <?php  echo $form->field($model, 'location') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'date') ?>
            </div>

            <div class="col-lg-6">
                <?php  echo $form->field($model, 'source') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'observer_id') ?>
            </div>

            <div class="col-lg-6">
                <?php  echo $form->field($model, 'description') ?>
            </div>
        </div>


<!--        --><?php // echo $form->field($model, 'uploaded_at') ?>

        <?php // echo $form->field($model, 'edited_at') ?>

        <div class="form-group">
            <?= Html::submitButton('Keresés', ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
