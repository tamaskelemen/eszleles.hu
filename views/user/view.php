<?php

use app\components\Badges;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $observations app\models\Observe[] */
/* @var $isOwn bool whether it is the profile of the visitor */
/* @var $badges array see Badges, only the earned ones on the profile of others */
/* @var $skyMapStats array|null null if there is nothing on the map */
/* @var $showsPlans bool whether the observing plans are switched on, see Features */
/* @var $plans app\models\ObservingPlan[] observing plans of the user, only the public ones for the others */
/* @var $planTotals int[] number of objects indexed by plan id */
/* @var $planDone int[] number of objects the user of the profile has observed indexed by plan id */

$this->title = $user->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$shareUrl = Url::to(['/user/profile', 'id' => $user->id], true);
$shareText = "{$user->name} észlelései a VCSE Észleléseken";

$this->registerMetaTag(['property' => 'og:url', 'content' => $shareUrl], 'og:url');
$this->registerMetaTag(['property' => 'og:type', 'content' => 'profile'], 'og:type');
$this->registerMetaTag([
    'property' => 'og:description',
    'content' => $user->introduction ?: $shareText,
], 'og:description');
// the latest observation with an image gives the preview
$previewImage = '/pictures/eszleloret-og.jpg';
foreach ($observations as $observation) {
    if ($observation->thumbnail) {
        $previewImage = $observation->getImagePath();
        break;
    }
}
$this->registerMetaTag(['property' => 'og:image', 'content' => Url::to($previewImage, true)], 'og:image');

// the highest one of every category
$topBadges = [];

foreach ($badges as $category) {
    $earned = Badges::getEarnedLevels($category);

    if (!empty($earned)) {
        $topBadges[] = [$category, end($earned)];
    }
}

// the programs can be shown on the map even if nothing is observed yet
$hasMap = $skyMapStats !== null || in_array(Badges::GROUP_PROGRAM, array_column($badges, 'group'), true);
?>
<div class="user-view container">
    <header class="profile">
        <div class="profile__main">
            <?= $this->render('//_common-items/_avatar', ['user' => $user, 'size' => 'large']) ?>

            <div class="profile__body">
                <div class="profile__title">
                    <h1><?= Html::encode($this->title) ?></h1>
                    <?= $this->render('//_common-items/_share', [
                        'url' => $shareUrl,
                        'title' => $this->title,
                        'text' => $shareText,
                        'label' => 'Profil megosztása',
                    ]) ?>
                </div>

                <?php if ($membership = $user->getMembershipDuration()) { ?>
                    <p class="profile__meta">
                        <i class="fa fa-calendar-o" aria-hidden="true"></i>
                        <?= Html::encode($membership) ?>
                    </p>
                <?php } ?>

                <p class="profile__meta">
                    <i class="fa fa-upload" aria-hidden="true"></i>
                    <?= count($observations) ?> észlelés
                </p>

                <?php if ($user->introduction) { ?>
                    <p class="profile__intro"><?= Html::encode($user->introduction) ?></p>
                <?php } ?>

                <?= $this->render('//_common-items/_social-links', ['user' => $user]) ?>

                <?php if ($topBadges) { ?>
                    <div class="badges__list" aria-label="Legmagasabb jelvények" role="group">
                        <?php foreach ($topBadges as list($category, $level)) { ?>
                            <?= $this->render('//_common-items/_badge', ['category' => $category, 'level' => $level]) ?>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>

        <?php if ($isOwn) { ?>
            <div class="profile__actions dropdown">
                <button class="btn btn-outline dropdown-toggle" type="button" id="profileSettings" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-cog" aria-hidden="true"></i>
                    Beállítások
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileSettings">
                    <?= Html::a('Adatok szerkesztése', ['update', 'id' => $user->id], ['class' => 'dropdown-item']) ?>
                    <?= Html::a('Jelszó megváltoztatása', ['change-password'], ['class' => 'dropdown-item']) ?>
                    <?= Html::a($user->avatar_path ? 'Profilkép szerkesztése' : 'Profilkép feltöltése', ['avatar'], ['class' => 'dropdown-item']) ?>
                    <?= Html::a('Helyszín', ['location'], ['class' => 'dropdown-item']) ?>
                    <div class="dropdown-divider"></div>
                    <?= Html::beginForm(['/site/logout'], 'post') ?>
                    <button class="dropdown-item" type="submit">Kilépés</button>
                    <?= Html::endForm() ?>
                </div>
            </div>
        <?php } ?>
    </header>

    <?= $this->render('_badges', ['categories' => $badges, 'isOwn' => $isOwn]) ?>

    <?php if ($showsPlans && ($plans || $isOwn)) { ?>
        <details class="profile-plans" id="tervek">
            <summary>
                <h2 class="profile__section">Észlelési tervek (<?= count($plans) ?>)</h2>
                <i class="fa fa-chevron-down" aria-hidden="true"></i>
            </summary>

            <?php if ($plans) { ?>
                <?= $this->render('//program/_plans', [
                    'plans' => $plans,
                    'totals' => $planTotals,
                    'done' => $planDone,
                    'showsOwner' => false,
                    'showsProgress' => true,
                    'showsMap' => $hasMap,
                ]) ?>
            <?php } else { ?>
                <p>Még nincs észlelési terved. Állíts össze egy listát a megfigyelni kívánt objektumokból!</p>
            <?php } ?>

            <?php if ($isOwn) { ?>
                <p><?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Új észlelési terv', ['/plan/create'], ['class' => 'btn btn-primary']) ?></p>
            <?php } ?>
        </details>
    <?php } ?>

    <?php if ($hasMap) { ?>
        <h2 class="profile__section" id="egterkep"><a class="anchor-link" href="#egterkep">Égtérkép</a></h2>

        <p class="skymap-credit">
            A térképen a nyilvánosan azonosított mélyég-objektumok szerepelnek. Források és az összes észlelő térképe:
            <?= Html::a('Égtérkép', ['/sky-map/index']) ?>.
        </p>

        <?= $this->render('//sky-map/_map', ['gaps' => [], 'userId' => $user->id]) ?>

        <?php if ($skyMapStats !== null) { ?>
            <?= $this->render('//sky-map/_stats', ['stats' => $skyMapStats, 'isSingleObserver' => true]) ?>
        <?php } ?>
    <?php } ?>

    <h2 class="profile__section" id="eszlelesek"><a class="anchor-link" href="#eszlelesek">Észlelések (<?= count($observations) ?>)</a></h2>

    <div class="obs-grid">
        <?php foreach ($observations as $observation) { ?>
            <div class="obs-cell">
                <?= $this->render('../_common-items/_listItem', ['model' => $observation]) ?>
            </div>
        <?php } ?>
    </div>
</div>
