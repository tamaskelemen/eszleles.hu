<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'introduction')->textarea() ?>

    <?= $form->field($model, 'website')->textInput(['maxlength' => true, 'type' => "url", 'pattern' => "https?://.+"]) ?>

    <?= $form->field($model, 'facebook')->textInput(['maxlength' => true, 'type' => "url", 'pattern' => "https?://.+"]) ?>

    <?php //temporarily disabling instagram, need to find good icon which matches in size with the other ?>
    <?php // $form->field($model, 'instagram')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'github')->textInput(['maxlength' => true, 'type' => "url", 'pattern' => "https?://.+"]) ?>

    <?= $form->field($model, 'newsletter')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Mentés', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
