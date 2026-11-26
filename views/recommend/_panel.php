<?php

use app\components\recommender\Recommender;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $recommender Recommender */

$context = $recommender->context;
$isGuest = $context->isGuest();
$values = $recommender->parseValues([]);

$this->registerJsFile('/js/recommender.js?v=' . filemtime(Yii::getAlias('@webroot/js/recommender.js')), ['depends' => \app\assets\AppAsset::class]);
?>
<section class="container section recommender" id="ajanlo" aria-labelledby="ajanlo-cim">
    <div class="section__head">
        <h2 id="ajanlo-cim"><a class="anchor-link" href="#ajanlo">Mit észleljek most?</a></h2>
    </div>

    <div class="recommender__layout">
        <div class="recommender__result">
            <div class="rec-result" id="rec-result" aria-live="polite" aria-busy="true">
                <p class="rec-loading">Keresek valamit, ami most jól látszik…</p>
            </div>
            <button class="btn btn-primary rec-next" type="button" id="rec-next">
                <i class="fa fa-random" aria-hidden="true"></i> Másikat kérek
            </button>
        </div>

        <form class="rec-filters panel<?= $isGuest ? ' rec-filters--locked' : '' ?>" id="rec-filters"
              data-url="<?= Url::to(['/recommend/next']) ?>" data-remember="<?= $isGuest ? '0' : '1' ?>">
            <?php if ($isGuest) { ?>
                <div class="rec-cta">
                    <p>Szeretnél személyre szabott tippeket?</p>
                    <?= Html::a('Lépj be!', ['/site/login'], ['class' => 'btn btn-primary btn-sm']) ?>
                </div>
            <?php } ?>

            <fieldset <?= $isGuest ? 'disabled' : '' ?>>
                <legend class="sr-only">Szűrők</legend>
                <?php foreach ($recommender->getFilters() as $key => $filter) { ?>
                    <div class="rec-filter rec-filter--<?= $key ?>">
                        <?= $this->render('filters/_' . $key, [
                            'filter' => $filter,
                            'value' => $values[$key],
                        ] + $filter->getViewParams($context)) ?>
                    </div>
                <?php } ?>

                <?php if (!$isGuest) { ?>
                    <button class="btn btn-link btn-sm rec-reset" type="reset"><i class="fa fa-undo" aria-hidden="true"></i> Szűrők az alapbeállításokra</button>
                <?php } ?>
            </fieldset>
        </form>
    </div>

    <p class="rec-credit">
        Objektumok és fényességadatok: <?= Html::a('OpenNGC', 'https://github.com/mattiaverga/OpenNGC') ?>, Mattia Verga
        (<?= Html::a('CC BY-SA 4.0', 'https://creativecommons.org/licenses/by-sa/4.0/') ?>), a többi katalógus forrása az
        <?= Html::a('égtérképen', ['/sky-map/index']) ?> olvasható.
    </p>
</section>
