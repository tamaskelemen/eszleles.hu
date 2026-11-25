<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model \app\models\forms\SocialSignupForm */
/* @var $providerName string */

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

$this->registerMetaTag([
    'name' => 'robots',
    'content' => 'noindex,nofollow']);

$this->title = 'Regisztráció befejezése';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup container container-narrow">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Már csak egy lépés van hátra a <?= Html::encode($providerName) ?> fiókkal történő regisztrációhoz:</p>

    <div class="panel">
        <div>
            <?php $form = ActiveForm::begin(['id' => 'form-social-signup']); ?>

            <?= $form->field($model, 'email')->textInput(['readonly' => !$model->isEmailEditable()]) ?>

            <?= $form->field($model, 'name')->textInput() ?>

            <?= $form->field($model, 'terms')->checkbox()
                ->label('A regisztrációval elismerem, hogy elolvastam és megértettem a <a href="/eula.pdf" target="_blank">VCSE Adatkezelési Tájékoztatóját</a>.')
            ?>

            <div class="form-group">
                <?= Html::submitButton('Regisztráció befejezése', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <p class="mt-4">
        Már regisztráltál korábban? Lépj be a meglévő profilodba, és összekapcsoljuk a <?= Html::encode($providerName) ?> fiókoddal.
    </p>
    <?= Html::beginForm(['/site/social-connect'], 'post') ?>
    <?= Html::submitButton('Már van profilom', ['class' => 'btn btn-outline']) ?>
    <?= Html::endForm() ?>
</div>
