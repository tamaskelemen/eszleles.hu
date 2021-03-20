<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $signupForm \app\models\forms\SignupForm */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Regisztráció';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Kérlek töltsd ki az alábbi mezőket a regisztrációhoz:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

            <?= $form->field($signupForm, 'email')->textInput(['autofocus' => true]) ?>

            <?= $form->field($signupForm, 'name')->textInput() ?>

            <?= $form->field($signupForm, 'password')->passwordInput() ?>

            <?= $form->field($signupForm, 'password_confirm')->passwordInput() ?>

            <div class="form-group">
                <?= Html::submitButton('Regisztráció elküldése', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
