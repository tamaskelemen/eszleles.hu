<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\assets\BootstrapAsset;
use app\assets\FontAwesomeAsset;
use app\models\Observe;

FontAwesomeAsset::register($this);
AppAsset::register($this);

$route = Yii::$app->controller->route;

$isBrowsing = $route === 'observe/index';
foreach (Observe::getAllTypes() as $type => $label) {
    $isBrowsing = $isBrowsing || $route === "{$type}/index";
}
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
    <script>
        try {
            if (localStorage.getItem('night-mode') === '1') {
                document.documentElement.setAttribute('data-night', '');
            }
        } catch (e) {}
    </script>
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

<a class="skip-link" href="#tartalom">Ugrás a tartalomra</a>

<nav class="navbar navbar-dark navbar-expand-lg sticky-top" aria-label="Főmenü">
    <div class="container">
        <a class="navbar-brand" href="<?= Url::toRoute('/') ?>">
            <img class="brand-logo" src="/brand-logo.png" alt="VCSE logo" >
            VCSE Észlelések
        </a>

        <?php if (!Yii::$app->user->isGuest) { ?>
            <?= $this->render('//_common-items/_notificationBell') ?>
        <?php } ?>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Menü megnyitása">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown <?= $isBrowsing ? 'active' : '' ?>">
                    <a class="nav-link dropdown-toggle" href="#" id="browseMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Böngészés
                    </a>
                    <div class="dropdown-menu" aria-labelledby="browseMenu">
                        <a class="dropdown-item" href="<?= Url::toRoute('/observe/index') ?>">Összes</a>
                        <div class="dropdown-divider"></div>
                        <?php foreach (Observe::getAllTypes() as $type => $label) { ?>
                            <a class="dropdown-item" href="<?= Url::toRoute("/{$type}/index") ?>">
                                <?= $this->render('//_common-items/_typeIcon', ['type' => $type]) ?>
                                <?= $label ?>
                            </a>
                        <?php } ?>
                    </div>
                </li>

                <li class="nav-item <?= in_array($route, ['observe/search']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/observe/search')?>">Keresés</a>
                </li>

                <li class="nav-item <?= in_array($route, ['sky-map/index']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/sky-map/index')?>">Égtérkép <span class="nav-beta">Béta</span></a>
                </li>

                <li class="nav-item <?= in_array($route, ['site/about']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/site/about')?>">Célok</a>
                </li>

                <li class="nav-item <?= in_array($route, ['site/faq']) ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= Url::toRoute('/site/faq')?>">GY.I.K.</a>
                </li>
            </ul>

            <ul class="navbar-nav navbar-actions">
                <li class="nav-item">
                    <button class="night-toggle" id="night-toggle" type="button" aria-pressed="false" title="Éjszakai mód: vörös fény, kíméli a sötéthez szokott szemet a távcső mellett">
                        <span class="night-toggle__lamp" aria-hidden="true"></span>
                        Éjszakai mód
                    </button>
                </li>

                <?php if (Yii::$app->user->isGuest) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::toRoute('/site/login') ?>">Belépés</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= Url::toRoute('/site/signup') ?>">Regisztráció</a>
                    </li>
                <?php } else { ?>
                    <li class="nav-item <?= in_array($route,['user/profile', 'user/index']) ? 'active' : '' ?>">
                        <a class="nav-link" href="<?= Url::toRoute(['/user/profile', 'id' => Yii::$app->user->getId() ]) ?>" title="<?= Html::encode(Yii::$app->user->getIdentity()->email) ?>">Profil</a>
                    </li>
                    <li class="nav-item">
                        <?= Html::beginForm(['/site/logout'], 'post') ?>
                        <button class="nav-link nav-button" type="submit">Kilépés</button>
                        <?= Html::endForm() ?>
                    </li>
                <?php } ?>

                <li class="nav-item dropdown">
                    <a class="btn btn-primary dropdown-toggle" href="#" id="uploadMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Feltöltés
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="uploadMenu">
                        <?php foreach (Observe::getAllTypes() as $type => $label) { ?>
                            <a class="dropdown-item" href="<?= Url::toRoute("/{$type}/create") ?>">
                                <?= $this->render('//_common-items/_typeIcon', ['type' => $type]) ?>
                                <?= $label ?>
                            </a>
                        <?php } ?>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main id="tartalom">
    <?= Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        'itemTemplate' => '<li class="breadcrumb-item">{link}</li>',
        'activeItemTemplate' => '<li class="breadcrumb-item active">{link}</li>',
        'options' => [
            'class' => 'container breadcrumb'
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
                            echo Html::encode($row);
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
</main>

<footer class="footer">
    <div class="container">
        <ul class="list-unstyled">
            <li><?= Html::a('Gyakran ismételt kérdések', Url::toRoute("/site/faq")) ?></li>
            <li><?= Html::a('Hibabejelentő', Url::toRoute("/bug-report/create"), ['target' => '_blank']) ?></li>
            <li><?= Html::a('Adatvédelmi nyilatkozat', Url::to("/eula.pdf"), ['target' => '_blank']) ?></li>
        </ul>
        <div>
            &copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?> - Üzemelteti a <?= Html::a('Vega Csillagászati Egyesület', 'http://vcse.hu', ['target' => '_blank']) ?>
        </div>
    </div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
