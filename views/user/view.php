<?php

use app\components\Badges;
use app\components\Helper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $user app\models\User */
/* @var $observations app\models\Observe[] */
/* @var $isOwn bool whether it is the profile of the visitor */
/* @var $badges array see Badges, only the earned ones on the profile of others */
/* @var $skyMapStats array|null null if there is nothing on the map */

$this->title = $user->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

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
        <div>
            <h1><?= Html::encode($this->title) ?></h1>

            <?php if ($user->introduction) { ?>
                <p class="profile__intro"><?= Html::encode($user->introduction) ?></p>
            <?php } ?>

            <p class="social">
                <?= Helper::isWebUrl($user->website) ? Html::a(Html::encode($user->website), $user->website) : Html::encode($user->website) ?>
                <?php foreach ($user->getSocialLinks() as $site => $link) {
                    if ($link) { ?>
                        <?= Html::a(
                            Html::img("/pictures/icons/$site.svg", ['alt'=>"$site logo"]),
                            $link
                        ) ?>
                    <?php }
                }
                ?>
            </p>

            <?php if ($topBadges) { ?>
                <div class="badges__list" aria-label="Legmagasabb jelvények" role="group">
                    <?php foreach ($topBadges as list($category, $level)) { ?>
                        <?= $this->render('//_common-items/_badge', ['category' => $category, 'level' => $level]) ?>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <?php if ($isOwn) { ?>
            <div class="profile__actions">
                <?= Html::a('Adatok szerkesztése', ['update', 'id' => $user->id], ['class' => 'btn btn-outline']) ?>
                <?= Html::a('Jelszó megváltoztatása', ['change-password'], ['class' => 'btn btn-outline']) ?>
            </div>
        <?php } ?>
    </header>

    <?= $this->render('_badges', ['categories' => $badges, 'isOwn' => $isOwn]) ?>

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
