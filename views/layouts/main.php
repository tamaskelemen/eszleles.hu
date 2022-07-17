<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\assets\BootstrapAsset;
use app\assets\FontAwesomeAsset;

FontAwesomeAsset::register($this);
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
    <meta property="og:author" content="Vega Csillagászati Egyesület" />
    <meta property="og:type" content="website" />

    <link rel="shortcut icon" href="/favicon_48x48.png?v=2" sizes="48x48" type="image/png" />
    <link rel="icon" href="/favicon_32x32.png?v=2" sizes="32x32" type="image/png" />
    <link rel="icon" href="/favicon_16x16.png?v=2" sizes="16x16" type="image/png" />
    <link rel="icon" href="/favicon.ico?v=2" type="image/x-icon">

    <?php $this->registerMetaTag([
            'name' => 'description',
            'content' => 'Csillagászati megfigyelések, amatőrcsillagász észlelések online gyűjtőhelye. ']) ?>
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <?php if (YII_ENV != 'dev') { ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-60MRD9RYES"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-60MRD9RYES');
    </script>
    <?php } ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <nav class="navbar navbar-dark navbar-expand-lg">
        <a class="navbar-brand" href="<?= Url::toRoute('/') ?>">
            <img class="brand-logo" src="/brand-logo.png" alt="VCSE logo" >
            VCSE Észlelések
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item <?= in_array($route, ['site/index', '/']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/')?>">Főoldal</a>
                </li>
                <li class="nav-item dropdown
                    <?= in_array(
                            $route,
                            [
                                'observe/index',
                                'deepsky/index',
                                'planet/index',
                                'moon/index',
                                'landscape/index',
                                'meteor/index',
                                'comet/index',
                                'sun/index',
                                'meteor/index',
                                'eclipse/index',
                            ]) ? 'active' : '' ?>">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Böngészés
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="<?= Url::toRoute('/observe/index') ?>">Összes</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= Url::toRoute('/deepsky/index') ?>">Mélyég</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/planet/index') ?>">Bolygók</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/moon/index') ?>">Hold</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/landscape/index') ?>">Asztrotájkép</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/comet/index') ?>">Üstökös</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/sun/index') ?>">Nap</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/meteor/index') ?>">Meteor</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/eclipse/index') ?>">Fogyatkozás</a>
                    </div>
                </li>

                <li class="nav-item <?= in_array($route, ['observe/search']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/observe/search')?>">Keresés</a>
                </li>

                <li class="nav-item <?= in_array($route, ['site/about']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/site/about')?>">Célok</a>
                </li>

                <li class="nav-item <?= in_array($route, ['site/faq']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/site/faq')?>">GY.I.K.</a>
                </li>

                <li class="nav-item dropdown <?= in_array($route, ['deepsky/create', 'planet/create', 'moon/create', 'meteor/create']) ? 'active' : ''  ?>">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Feltöltés
                    </a>

                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="<?= Url::toRoute('/deepsky/create') ?>">Mélyég</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/planet/create') ?>">Bolygók</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/moon/create') ?>">Hold</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/landscape/create') ?>">Asztrotájkép</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/comet/create') ?>">Üstökös</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/sun/create') ?>">Nap</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/meteor/create') ?>">Meteor</a>
                        <a class="dropdown-item" href="<?= Url::toRoute('/eclipse/create') ?>">Fogyatkozás</a>
                    </div>
                </li>


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
                <ul class="navbar-nav float-right">
                    <li class="nav-item <?= in_array($route,['user/profile', 'user/index']) ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= Url::toRoute(['/user/profile', 'id' => Yii::$app->user->getId() ]) ?>">Profil (<?= Yii::$app->user->getIdentity()->email ?>)</a>
                    </li>
                </ul>
               <?= Html::beginForm(['/site/logout'], 'post') ?>
                <button class="btn btn-success" type="submit">
                    Kilépés
                </button>
                <?= Html::endForm() ?>

            <?php } ?>
        </div>
    </nav>

    <div class="wrap">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
            'activeItemTemplate' => '<li class="breadcrumb-item active">{link}</li>',
            'options' => [
                'class' => 'container breadcrumb padding-fix'
            ],
            'homeLink' => [
                'label' => 'Főoldal',
                'url' => '/',
            ],
        ]) ?>

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
    <ul class="list-unstyled">
        <li class="d-inline pr-4"><?= Html::a('Gyakran ismételt kérdések', Url::toRoute("/site/faq")) ?></li>
        <li class="d-inline pr-4"><?= Html::a('Hibabejelentő', Url::toRoute("/bug-report/create"), ['target' => '_blank']) ?></li>
        <li class="d-inline"><?= Html::a('Adatvédelmi nyilatkozat', Url::to("/eula.pdf"), ['target' => '_blank']) ?></li>
    </ul>
    <div class="pb-2">
        &copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?> - Üzemelteti a <?= Html::a('Vega Csillagászati Egyesület', 'http://vcse.hu', ['target' => '_blank']) ?>
    </div>

</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
