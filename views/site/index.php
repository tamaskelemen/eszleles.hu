<?php

/* @var $this yii\web\View */

use yii\helpers\Url;

$this->title = 'VCSE Észlelések';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Észlelés.hu</h1>

        <p class="lead">Csillagászati megfigyelések gyűjtőhelye.</p>
        <?php if (Yii::$app->user->isGuest) { ?>
            <p><a class="btn btn-lg btn-success" href="<?= Url::toRoute('/site/signup') ?>">Regisztrálok</a></p>
            <p>vagy ha már regisztráltál, <a class="" href="<?= Url::toRoute('/site/login') ?>">lépj be</a>.</p>
        <?php } ?>
    </div>

    <div class="body-content">

        <h2>Legújabb feltöltések</h2>
        <div class="row">
            <div class="col-lg-4">
                <h3>Heading</h3>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/doc/">Yii Documentation &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h3>Heading</h3>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/forum/">Yii Forum &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h3>Heading</h3>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/extensions/">Yii Extensions &raquo;</a></p>
            </div>
        </div>

    </div>
</div>
