<?php

use app\models\Image;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\LandscapeForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="observe-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-lg-6 col-12">
            <?= $form->field($model, 'object_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'telescope')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'date')->widget(DatePicker::class, [
                'options' => ['autocomplete' => 'off'],
                'pickerIcon' => '<i class="fas fa-calendar-alt text-primary"></i>',
                'removeIcon' => '<i class="fas fa-trash text-danger"></i>',
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
            <?php if ($model->image !== null) { ?>
                <div class="img-container">
                    <?= Html::img($model->image->path, ['alt' => $model->object_name, 'class'=> 'img-fluid img-view m-auto']) ?>
                </div>
                <?php
            } else { ?>
                <?= $form->field($model, 'image')->fileInput([
                        "class" => "form-control-file",
                ]) ?>
                <ul class="upload-description">
                    <li>
                        A feltöltött kép mérete maximum <?= Image::getUploadSizeLimit() ?>Mb lehet
                    </li>

                    <li>
                        Támogatott kiterjesztések: <?= Image::SUPPORTED_EXTENSIONS ?>
                    </li>
                </ul>
            <?php } ?>

            <?= $form->field($model, 'camera')->textInput() ?>

            <?= $form->field($model, 'mechanics')->textInput() ?>

            <?= $form->field($model, 'expo')->textInput() ?>

            <?= $form->field($model, 'filter')->textInput() ?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Feltöltés', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
