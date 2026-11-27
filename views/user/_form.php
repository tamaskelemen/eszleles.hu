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

    <?php foreach (\app\models\User::LINK_ATTRIBUTES as $attribute) { ?>
        <?= $form->field($model, $attribute)->textInput([
            'maxlength' => true,
            'type' => 'url',
            'pattern' => 'https?://.+',
            'placeholder' => 'https://…',
        ]) ?>
    <?php } ?>

    <?= $form->field($model, 'newsletter')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Mentés', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
