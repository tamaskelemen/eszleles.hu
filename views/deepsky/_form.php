<?php

use app\widgets\DatePicker;
use app\widgets\PreviousValueInput;
use app\widgets\SkyObjectInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\DeepSkyForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="observe-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-lg-6 col-12">
            <?= $form->field($model, 'object_name')->textInput(['maxlength' => true]) ?>

            <?= SkyObjectInput::widget(['model' => $model]) ?>

            <?= $form->field($model, 'telescope')->widget(PreviousValueInput::class, ['options' => ['maxlength' => true]]) ?>

            <?= $form->field($model, 'location')->widget(PreviousValueInput::class, ['options' => ['maxlength' => true]]) ?>

            <?= $form->field($model, 'date')->widget(DatePicker::class, [
                'options' => ['autocomplete' => 'off'],
                'pluginOptions' => [
                    'format' => 'yyyy-mm-dd',
                    'autoclose' => true
                ]
            ]) ?>

            <?= $form->field($model, 'seeing')->textInput() ?>

            <?= $form->field($model, 'transparency')->textInput() ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
        </div>

        <div class="col-lg-6 col-12">
            <?= $this->render("//observe/_image-upload", ["form" => $form, "model" => $model]) ?>

            <?= $form->field($model, 'camera')->widget(PreviousValueInput::class) ?>

            <?= $form->field($model, 'mechanics')->widget(PreviousValueInput::class) ?>

            <?= $form->field($model, 'expo')->textInput() ?>

            <?= $form->field($model, 'filter')->textInput() ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Feltöltés', ['class' => 'btn btn-primary btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
