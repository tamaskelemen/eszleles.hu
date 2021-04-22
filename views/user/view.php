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
    <div class="text-center">
        <h1>
            <?= Html::encode($this->title) ?>
        </h1>

        <p>
            <?= $user->website ? Html::a($user->website, Url::to($user->website, true)) : "" ?>
        </p>

        <p>
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

        <p>
            <?php if (!Yii::$app->user->isGuest && $user->id == Yii::$app->user->getIdentity()->id) { ?>
            <?= Html::a('Adatok szerkesztése', ['update', 'id' => $user->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Jelszó megváltoztatása', ['change-password'], ['class' => 'btn btn-primary']) ?>
            <?php } ?>
        </p>
    </div>

    <div class="row">
        <?php if ($user->introduction) { ?>
        <div class="col-12">

            <h5 class="text-white"><?= $user->getAttributeLabel('introduction') ?> </h5>
            <?= Html::encode($user->introduction) ?>
        </div>
        <?php } ?>
    </div>
    <hr>
    <div class="row">

            <?php foreach ($observations as $observation) {
                echo $this->render('../_common-items/_latestItem', ['model' => $observation]);
            } ?>
    </div>
</div>
