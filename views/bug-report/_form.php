<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use \app\models\BugReport;

/* @var $this yii\web\View */
/* @var $model app\models\BugReport */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bug-report-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'type')->dropDownList(BugReport::getTypes()) ?>

    <?php if (Yii::$app->user->identity->is_admin) {
        echo $form->field($model, 'status')->dropDownList(BugReport::getStatuses());
    } ?>

    <div class="form-group">
        <?= Html::submitButton('Mentés', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
