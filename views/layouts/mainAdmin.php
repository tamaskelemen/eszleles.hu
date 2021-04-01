<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
$route = Yii::$app->controller->route;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="<?= Html::encode($this->title)?>" />

    <meta name="robots" content="index, follow">
    <?php $this->registerMetaTag([
        'name' => 'description',
        'content' => 'Csillagászati megfigyelések, amatőrcsillagász észlelések online gyűjtőhelye.. ']) ?>
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <nav class="navbar navbar-dark navbar-expand-lg">
        <a class="navbar-brand" href="<?= Url::toRoute('/') ?>">VCSE Észlelések <span style="color: red">ADMIN</span></a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item <?= in_array($route, ['site/index', '/']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/')?>">Főoldal</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?= Url::toRoute('/bug-report/index')?>">Hibajelentések</a>
                </li>

                <?php if (!Yii::$app->user->isGuest) { ?>
                    <li class="nav-item dropdown <?= in_array($route, ['deepsky/create', 'planet/create', 'moon/create', 'meteor/create']) ? 'active' : ''  ?>">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Feltöltés
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="<?= Url::toRoute('/deepsky/create') ?>">Mélyég</a>
                            <a class="dropdown-item" href="<?= Url::toRoute('/planet/create') ?>">Bolygók</a>
                            <a class="dropdown-item" href="<?= Url::toRoute('/moon/create') ?>">Hold</a>
                            <a class="dropdown-item" href="<?= Url::toRoute('/meteor/create') ?>">Meteor</a>
                        </div>
                    </li>


                    <li class="nav-item <?= in_array($route,['user/profile', 'user/index']) ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= Url::toRoute(['/user/profile', 'id' => Yii::$app->user->getId() ]) ?>">Profil (<?= Yii::$app->user->getIdentity()->email ?>)</a>
                    </li>
                <?php } ?>
            </ul>

            <?php if (Yii::$app->user->isGuest) { ?>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item login">
                        <a href="<?= Url::toRoute('/site/login') ?>" class="btn btn-success">
                            Belépés
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= Url::toRoute('/site/signup') ?>" class="btn btn-success">
                            Regisztráció
                        </a>
                    </li>
                </ul>

            <?php } else { ?>
                <?= Html::beginForm(['/site/logout'], 'post') ?>
                <button class="btn btn-success" type="submit">
                    Kilépés
                </button>
                <?= Html::endForm() ?>

            <?php } ?>
        </div>
    </nav>

    <div class="container-fluid">

        <?php
        $flashes = Yii::$app->session->getAllFlashes();
        if (!empty($flashes)) {
            foreach ($flashes as $key => $value) { ?>
                <div class="container">
                    <div class="alert-<?= $key ?> alert alert-dismissible" role="alert">
                        <?php
                        foreach ($value as $row) {
                            echo $row;
                        }
                        ?>
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
                    </div>
                </div>
                <?php
            }
        }
        ?>

        <?= $content ?>
    </div>
</div>

<footer class="footer text-center">
    <div>
        &copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?> - Üzemelteti a <?= Html::a('Vega Csillagászati Egyesület', 'http://vcse.hu', ['target' => '_blank']) ?>
    </div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
