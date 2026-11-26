<?php

use app\components\SocialLogin;
use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\forms\AvatarForm */
/* @var $user app\models\User */

$this->title = $user->avatar_path ? 'Profilkép szerkesztése' : 'Profilkép feltöltése';
$this->params['breadcrumbs'][] = ['label' => 'Felhasználók', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $user->name, 'url' => ['profile', 'id' => $user->id]];
$this->params['breadcrumbs'][] = 'Profilkép';
?>
<div class="users-update">
    <div class="container container-narrow">
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="avatar-edit">
            <?= $this->render('//_common-items/_avatar', ['user' => $user, 'size' => 'large']) ?>

            <div>
                <?php if ($user->avatar_path && !in_array($user->avatar_source, [User::AVATAR_SOURCE_UPLOAD, null], true)) { ?>
                    <p class="text-muted">
                        Ezt a képet a <?= Html::encode(SocialLogin::getProviderName($user->avatar_source)) ?> fiókodból vettük át.
                        Ha feltöltesz egy másikat vagy törlöd, többé nem cseréljük le.
                    </p>
                <?php } ?>

                <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

                <?= $form->field($model, 'image')->fileInput(['accept' => 'image/jpeg,image/png'])
                    ->label($user->avatar_path ? 'Új kép' : 'Kép')
                    ->hint('JPG vagy PNG, legfeljebb 10 MB. A kép közepéből négyzetes részt vágunk ki.') ?>

                <div class="form-group">
                    <?= Html::submitButton($user->avatar_path ? 'Csere' : 'Feltöltés', ['class' => 'btn btn-primary']) ?>
                </div>

                <?php ActiveForm::end(); ?>

                <?php if ($user->avatar_path) { ?>
                    <?= Html::beginForm(['delete-avatar'], 'post') ?>
                    <?= Html::submitButton('Profilkép törlése', [
                        'class' => 'btn btn-outline',
                        'data-confirm' => 'Biztosan törlöd a profilképedet?',
                    ]) ?>
                    <?= Html::endForm() ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
