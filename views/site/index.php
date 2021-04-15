<?php

/* @var $this yii\web\View */
/* @var $latestObs Observe[] */

use app\models\Observe;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'VCSE Észlelések';
?>
<div class="site-index">

    <div class="jumbotron text-white">
        <h1>Észlelés.hu</h1>

        <p class="lead">Csillagászati megfigyelések gyűjtőhelye.</p>
        <?php if (Yii::$app->user->isGuest) { ?>
            <p><a class="btn btn-lg btn-success" href="<?= Url::toRoute('/site/signup') ?>">Regisztrálok</a></p>
            <p>vagy ha már regisztráltál, <a class="" href="<?= Url::toRoute('/site/login') ?>">lépj be</a>.</p>
        <?php } ?>
    </div>

    <div class="body-content container">

        <h2>Hírek</h2>
        <p><i>2021-04-25</i></p>
        <div class="row">
            <div class="col">
                <h5>Elindult az észlelésfeltöltő!</h5>
                <p>
                    A Vega Csillagászati egyesület elindította a saját észlelésfeltöltőjét,
                    nem csak a tagjainak. Bárki ingyenesen regisztrálhat, és feltöltheti
                    akár képes, akár szöveges észleléseit, asztrofotóit.
                </p>
            </div>
        </div>

        <h2 class="pt-4">Legújabb feltöltések</h2>
        <div class="row">
            <?php
            foreach ($latestObs as $obs) {
                echo $this->render('../_common-items/_latestItem', ['model' => $obs]);
            }
            ?>
        </div>

    </div>
</div>
