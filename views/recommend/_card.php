<?php

use app\components\Astro;
use app\components\Data;
use app\models\Observe;
use app\models\SkyObject;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $object SkyObject|null */
/* @var $context app\components\recommender\Context */
/* @var $count int objects passing the filters */
/* @var $designations string[] */
/* @var $programs string[] */
/* @var $observations Observe[] of the user */
/* @var $images Observe[] */
/* @var $observationCount int */

$number = function ($value, $decimals = 1) {
    return str_replace('-', '−', number_format($value, $decimals, ',', ' '));
};
$moon = $context->moon;
?>
<?php if ($object === null) { ?>
    <div class="rec-empty">
        <p><strong>Most egyetlen objektum sem felel meg a feltételeknek.</strong></p>
        <p>Lazíts egy kicsit a szűrőkön, például engedj meg halványabb vagy alacsonyabban álló objektumokat.</p>
    </div>
<?php } else {
    $position = Astro::horizontal($object->ra, $object->dec, $context->latitude, $context->lst);
    $isPast = Astro::hourAngle($object->ra, $context->lst) < 180;
    $transits = Astro::transits($object->ra, $context->lst, $context->timestamp);
    $meridianAltitude = Astro::meridianAltitude($object->dec, $context->latitude);
    $isCircumpolar = $object->dec > 90 - $context->latitude;
    ?>
    <article class="rec-card" data-id="<?= $object->id ?>">
        <div class="rec-card__main">
            <p class="rec-card__kind">
                <?= Html::encode(SkyObject::getTypeName($object->type)) ?> ·
                <?= Html::encode(Data::listConstellation()[$object->constellation] ?? $object->constellation) ?>
            </p>
            <h3 class="rec-card__name"><?= Html::encode($object->getDisplayName()) ?></h3>
            <?php if (!empty($designations)) { ?>
                <p class="rec-card__designations"><?= Html::encode(implode(' · ', $designations)) ?></p>
            <?php } ?>

            <?php if (!empty($programs)) { ?>
                <ul class="rec-card__programs" aria-label="Észlelőprogramok">
                    <?php foreach ($programs as $program) { ?>
                        <li><?= Html::encode($program) ?></li>
                    <?php } ?>
                </ul>
            <?php } ?>

            <dl class="rec-facts">
                <div>
                    <dt>Most</dt>
                    <dd><?= $number($position['altitude'], 0) ?>° magasan, <?= Astro::getDirectionName($position['azimuth']) ?></dd>
                </div>
                <div>
                    <dt>Delelés</dt>
                    <dd>
                        <?php if ($isPast) { ?>
                            már delelt, <?= date('H:i', $transits['previous']) ?>-kor
                        <?php } else { ?>
                            még nem delelt, <?= date('H:i', $transits['next']) ?>-kor delel
                        <?php } ?>
                        <br><?= $number($meridianAltitude, 0) ?>° magasan<?= $isCircumpolar ? ', sosem nyugszik le' : '' ?>
                    </dd>
                </div>
                <div class="rec-facts__small">
                    <dt>Koordináták (J2000)</dt>
                    <dd>RA <?= Astro::formatRa($object->ra) ?><br>Dec <?= Astro::formatDec($object->dec) ?></dd>
                </div>
                <div>
                    <dt>Fényesség</dt>
                    <?php if ($object->magnitude === null) { ?>
                        <dd class="not-set">nincs fényességadat</dd>
                    <?php } else { ?>
                        <dd><?= $number($object->magnitude) ?> mag</dd>
                    <?php } ?>
                </div>
                <div class="rec-facts__small">
                    <dt>Felületi fényesség</dt>
                    <?php if ($object->surface_brightness === null) { ?>
                        <dd class="not-set">nincs adat</dd>
                    <?php } else { ?>
                        <dd><?= $number($object->surface_brightness) ?> mag/″²</dd>
                    <?php } ?>
                </div>
                <div>
                    <dt>Látszó méret</dt>
                    <?php if ($object->size === null) { ?>
                        <dd class="not-set">nincs adat</dd>
                    <?php } else { ?>
                        <dd><?= $number($object->size) ?>′<?= $object->min_axis ? ' × ' . $number($object->min_axis) . '′' : '' ?></dd>
                    <?php } ?>
                </div>
            </dl>

            <?php if (!Yii::$app->user->isGuest) { ?>
                <?php if (empty($observations)) { ?>
                    <p class="rec-status">Még nem töltöttél fel észlelést erről az objektumról.</p>
                <?php } else { ?>
                    <p class="rec-status rec-status--done">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        Már észlelted:
                        <?= implode(', ', array_map(function (Observe $observe) {
                            return Html::a(Html::encode(substr((string)$observe->date, 0, 10)), $observe->getViewUrl());
                        }, $observations)) ?>
                    </p>
                <?php } ?>
            <?php } ?>

            <p class="rec-card__links">
                <?= Html::a('Megnézem az égtérképen', $object->getMapUrl()) ?>
            </p>
        </div>

        <div class="rec-card__media">
            <?php if (empty($images)) { ?>
                <p class="rec-card__no-image">
                    <?= $observationCount > 0 ? 'Kép még nincs róla az oldalon.' : 'Még senki nem töltött fel észlelést róla – légy te az első!' ?>
                </p>
            <?php } else { ?>
                <div class="rec-card__images rec-card__images--<?= min(count($images), 2) ?>">
                    <?php foreach ($images as $image) { ?>
                        <a href="<?= Url::to($image->getViewUrl()) ?>" title="<?= Html::encode($image->object_name . ' – ' . ($image->observer ? $image->observer->name : '')) ?>">
                            <img src="<?= Html::encode($image->getThumbnailPath()) ?>" alt="<?= Html::encode($image->object_name) ?>" loading="lazy">
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($observationCount > 0) { ?>
                <p class="rec-card__count"><?= $observationCount ?> észlelés az oldalon</p>
            <?php } ?>
        </div>
    </article>
<?php } ?>

<p class="rec-context">
    <span><i class="fa fa-map-marker" aria-hidden="true"></i>
        <?= Html::encode($context->placeName) ?>
        (<?= $number(abs($context->latitude), 2) ?>° <?= $context->latitude >= 0 ? 'É' : 'D' ?>,
        <?= $number(abs($context->longitude), 2) ?>° <?= $context->longitude >= 0 ? 'K' : 'Ny' ?>)
        <?php if (!$context->isGuest()) { ?>
            · <?= Html::a($context->isDefaultPlace ? 'saját helyszín megadása' : 'módosítás', ['/user/location']) ?>
        <?php } ?></span>
    <span><?= date('Y. m. d. H:i', $context->timestamp) ?></span>
    <span><?= Astro::getTwilightName($context->sunAltitude) ?></span>
    <span>Hold: <?= round($moon['fraction'] * 100) ?>%, <?= $moon['isWaxing'] ? 'növő' : 'fogyó' ?>,
        <?= $moon['altitude'] > 0 ? $number($moon['altitude'], 0) . '° magasan' : 'a horizont alatt' ?></span>
    <span><?= $count ?> objektum felel meg a feltételeknek</span>
</p>
<?php if ($context->sunAltitude > -12) { ?>
    <p class="rec-warning">Még nincs teljesen sötét, a halványabb objektumok most nem látszanak.</p>
<?php } ?>
