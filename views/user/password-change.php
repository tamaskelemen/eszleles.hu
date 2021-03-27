<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\forms\PasswordChangeForm */

$this->title = 'Jelszó módosítása';
$this->params['breadcrumbs'][] = ['label' => 'Felhasználók', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::$app->user->getIdentity()->name, 'url' => ['profile', 'id' => Yii::$app->user->getIdentity()->getId() ]];
$this->params['breadcrumbs'][] = 'Módosítás';
?>
<div class="users-update">
    <div class="text-center">
        <h1>
            <?= Html::encode($this->title) ?>
        </h1>
    </div>

    <div class="container">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <?= $form->field($model, 'password_confirm')->passwordInput() ?>

        <div class="form-group">
            <?= Html::submitButton('Mentés', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
