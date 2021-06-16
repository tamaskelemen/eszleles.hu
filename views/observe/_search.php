<?php

use app\widgets\DatePicker;
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
            'action' => ['/observe/search'],
            'method' => 'get',
        ]); ?>
        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'object_name') ?>
            </div>

            <div class="col-lg-6">
                <?php  echo $form->field($model, 'type')->dropDownList(array_merge(['' => ''],\app\models\Observe::getAllTypes())) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'telescope') ?>
            </div>

            <div class="col-lg-6">
                <?php  echo $form->field($model, 'mechanics') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'seeing') ?>
            </div>

            <div class="col-lg-6">
                <?php  echo $form->field($model, 'transparency') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'description') ?>
            </div>

            <div class="col-lg-6">
                <?php  echo $form->field($model, 'location') ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'fromDate')->widget(DatePicker::class, [
                    'options' => ['autocomplete' => 'off'],
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'autoclose' => true
                    ]
                ]) ?>
            </div>

            <div class="col-lg-6">
                <?= $form->field($model, 'toDate')->widget(DatePicker::class, [
                    'options' => ['autocomplete' => 'off'],
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'autoclose' => true
                    ]
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'fromUploadDate')->widget(DatePicker::class, [
                    'options' => ['autocomplete' => 'off'],
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'autoclose' => true
                    ]
                ]) ?>
            </div>

            <div class="col-lg-6">
                <?= $form->field($model, 'toUploadDate')->widget(DatePicker::class, [
                    'options' => ['autocomplete' => 'off'],
                    'pluginOptions' => [
                        'format' => 'yyyy-mm-dd',
                        'autoclose' => true
                    ]
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <?php  echo $form->field($model, 'observer_id') ?>
            </div>
        </div>


<!--        --><?php // echo $form->field($model, 'uploaded_at') ?>

        <?php // echo $form->field($model, 'edited_at') ?>

        <div class="form-group">
            <?= Html::submitButton('Keresés', ['class' => 'btn btn-success']) ?>
            <?= Html::resetButton('Törlés', ['class' => 'btn btn-info']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
