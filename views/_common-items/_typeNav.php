<?php

use app\models\Observe;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$route = Yii::$app->controller->route;

$this->registerJsFile('/js/type-nav.js?v=' . filemtime(Yii::getAlias('@webroot/js/type-nav.js')), ['depends' => 'app\assets\AppAsset']);
?>
<div class="type-nav-wrap">
    <button class="type-nav__arrow type-nav__arrow--prev" type="button" tabindex="-1" aria-label="Előző kategóriák" hidden></button>
    <nav class="type-nav" aria-label="Észleléstípusok">
        <?= Html::a('Összes', Url::toRoute('/observe/index'), [
            'class' => $route === 'observe/index' ? 'active' : '',
        ]) ?>
        <?php foreach (Observe::getAllTypes() as $type => $label) { ?>
            <a href="<?= Url::toRoute("/{$type}/index") ?>" class="<?= $route === "{$type}/index" ? 'active' : '' ?>">
                <?= $this->render('_typeIcon', ['type' => $type]) ?>
                <?= $label ?>
            </a>
        <?php } ?>
    </nav>
    <button class="type-nav__arrow type-nav__arrow--next" type="button" tabindex="-1" aria-label="További kategóriák" hidden></button>
</div>
