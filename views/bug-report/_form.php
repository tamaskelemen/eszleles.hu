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

    <?= $form->field($model, 'title')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'type')->dropDownList(BugReport::getTypes()) ?>
    <ul>
        <li><b>feature</b>: Ötlet, kívánság, mit szeretnél látni az oldalon, hogyan lehetne bővíteni, javítani.</li>
        <li><b>bug</b>: Hibás működés, elgépelt címszavak, halott linkek, a reprodukciós lépésekkel vagy leírással, hogy mi is meg tudjuk találni azt.</li>
    </ul>

    <?php if (Yii::$app->user->identity->is_admin) {
        echo $form->field($model, 'status')->dropDownList(BugReport::getStatuses());
    } ?>

    <div class="form-group">
        <?= Html::submitButton('Mentés', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
