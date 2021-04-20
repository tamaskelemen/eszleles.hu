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
        <h2 class="pt-4">Legújabb feltöltések</h2>

        <div class="row">
            <?php
            foreach ($latestObs as $obs) {
                echo $this->render('../_common-items/_latestItem', ['model' => $obs]);
            }
            ?>
        </div>
        <hr>
        <h2 class="pb-4">Hírek</h2>
        <div class="row mb-5">
            <div class="col-12">
                <h5>VEGA észlelések feltöltve</h5>
                <p><i>2021-04-20</i></p>

                <p>
                    A <?= Html::a('VEGA', 'http://vcse.hu/vega-lista/') ?> a Vega Csillagászati egyesület folyóirata, amelyben évtizedek óta publikálva vannak az
                    amatőrcsillagászati megfigyelések. Ezeket összegyűjtve, rendezett formában elérhetővé tettük
                    weboldalunkon.
                </p>
                <p>
                    A VEGA-ban közel 30 évre visszamenőleg találhatunk amatőrcsillagász megfigyeléseket.
                    Most végre lehetőség nyílik hosszú idő távlatából újraolvasni
                    egy-egy adott csillaghalmaz, üstökös, vagy épp nóva megfigyelést.
                    Érdekes lehet felfedezni, az idő folyamán milyen változások
                    látszanak - akár a természet változása, akár a távcsövek, eszközök
                    fejlődése miatt.
                </p>
            </div>
            <hr>
            <div class="col-12">
                <h5>Elindult az észlelésfeltöltő!</h5>
                <p><i>2021-04-19</i></p>

                <p>
                    A <?= Html::a('Vega Csillagászati Egyesület', 'http://vcse.hu') ?> elindította a saját észlelésfeltöltőjét,
                    nem csak a tagjainak. Bárki ingyenesen regisztrálhat, és feltöltheti
                    akár képes, akár szöveges észleléseit, asztrofotóit. Célunk, hogy
                    hosszútávra és szervezetten, kereshető formában megőrizzük a
                    magyar amatőrcsillagász társadalom hosszú évek
                    alatt gyűjtött megfigyeléseit.
                </p>
                <p>
                    Folyamatosan fejlesztjük az oldalt.<br>
                    Az oldalon szabadidőnkben, bármilyen profit nélkül dolgozunk,
                    de a terveink listája hosszú. Ahogy időnk engedi, egyre több
                    funkcióval szeretnénk ellátni oldalunk (amatőr)csillagász társaink
                    örömére
                </p>
            </div>
        </div>

    </div>
</div>
