<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $observations app\models\Observe[] */

$this->title = $user->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-view container">
    <header class="profile">
        <div>
            <h1><?= Html::encode($this->title) ?></h1>

            <?php if ($user->introduction) { ?>
                <p class="profile__intro"><?= Html::encode($user->introduction) ?></p>
            <?php } ?>

            <p class="social">
                <?= $user->website ? Html::a($user->website, Url::to($user->website, true)) : "" ?>
                <?php foreach ($user->getSocialLinks() as $site => $link) {
                    if ($link) { ?>
                        <?= Html::a(
                            Html::img("/pictures/icons/$site.svg", ['alt'=>"$site logo"]),
                            $link
                        ) ?>
                    <?php }
                }
                ?>
            </p>
        </div>

        <?php if (!Yii::$app->user->isGuest && $user->id == Yii::$app->user->getIdentity()->id) { ?>
            <div class="profile__actions">
                <?= Html::a('Adatok szerkesztése', ['update', 'id' => $user->id], ['class' => 'btn btn-outline']) ?>
                <?= Html::a('Jelszó megváltoztatása', ['change-password'], ['class' => 'btn btn-outline']) ?>
            </div>
        <?php } ?>
    </header>

    <h2>Észlelések (<?= count($observations) ?>)</h2>

    <div class="obs-grid">
        <?php foreach ($observations as $observation) { ?>
            <div class="obs-cell">
                <?= $this->render('../_common-items/_listItem', ['model' => $observation]) ?>
            </div>
        <?php } ?>
    </div>
</div>
