<?php

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\MoonForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="observe-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'object_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'image')->fileInput() ?>

    <?= $form->field($model, 'moon_phase')->textInput() ?>

    <?= $form->field($model, 'telescope')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mechanics')->textInput() ?>

    <!--    --><?php //= $form->field($model, 'camera')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'seeing')->textInput() ?>

    <?= $form->field($model, 'transparency')->textInput() ?>

    <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date')->widget(DatePicker::class, [
        'options' => ['autocomplete' => 'off'],
        'pickerIcon' => '<i class="fas fa-calendar-alt text-primary"></i>',
        'removeIcon' => '<i class="fas fa-trash text-danger"></i>',
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
            'autoclose'=>true
        ]
    ]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
