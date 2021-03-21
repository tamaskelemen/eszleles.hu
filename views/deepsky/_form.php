<?php

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\DeepSkyForm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="observe-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'object_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'image')->fileInput() ?>

    <?= $form->field($model, 'constellation')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'object_type')->dropDownList(\app\components\Data::listObjectType()) ?>

    <?= $form->field($model, 'telescope')->textInput(['maxlength' => true]) ?>

    <!--    --><?php //= $form->field($model, 'camera')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'seeing')->textInput() ?>

    <?= $form->field($model, 'transparency')->textInput() ?>

    <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>

    <?= DatePicker::widget([
        'name' => 'date',
        'model'=> $model,
        'attribute' => 'date',
        'pluginOptions' => [
            'format' => 'yyyy-mm-dd',
        ]
    ]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
