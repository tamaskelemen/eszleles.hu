<?php

use app\components\Badges;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $categories array see Badges, without progress if it is not the own profile of the visitor */
/* @var $isOwn bool */

$groups = [
    Badges::GROUP_PROGRAM => 'Észlelőprogramok',
    Badges::GROUP_ACTIVITY => 'Jelvények',
];

$sectionIds = [
    Badges::GROUP_PROGRAM => 'eszleloprogramok',
    Badges::GROUP_ACTIVITY => 'jelvenyek',
];
?>
<?php foreach ($groups as $group => $title) {
    $groupCategories = array_filter($categories, function ($category) use ($group) {
        return $category['group'] === $group;
    });

    if (empty($groupCategories)) {
        continue;
    }
    ?>
    <section class="badges" id="<?= $sectionIds[$group] ?>" aria-labelledby="badges-<?= $group ?>">
        <h2 id="badges-<?= $group ?>"><a class="anchor-link" href="#<?= $sectionIds[$group] ?>"><?= $title ?></a></h2>

        <?php if ($isOwn) { ?>
            <p class="skymap-credit">
                A hiányzó jelvényeket és az előrehaladásodat csak te látod.
                <?php if ($group === Badges::GROUP_PROGRAM) { ?>
                    Az azonosított objektumok számítanak, a listák itt vannak: <?= Html::a('Észlelőprogramok', ['/program/index']) ?>.
                <?php } ?>
            </p>
        <?php } ?>

        <div class="badges__grid">
            <?php foreach ($groupCategories as $category) { ?>
                <div class="badges__card">
                    <h3 class="badges__title">
                        <?= $group === Badges::GROUP_PROGRAM
                            ? Html::a(Html::encode($category['name']), ['/program/view', 'key' => $category['key']])
                            : Html::encode($category['name']) ?>
                        <?php if ($isOwn && $category['note']) { ?>
                            <?= $this->render('//_common-items/_infoTip', ['label' => 'Mi számít bele?', 'text' => $category['note']]) ?>
                        <?php } ?>
                        <?php if ($group === Badges::GROUP_PROGRAM) { ?>
                            <?= Html::button('<i class="fa fa-globe" aria-hidden="true"></i>', [
                                'class' => 'info-tip badges__map',
                                'title' => 'Mutasd a térképen',
                                'aria-label' => $category['name'] . ': Mutasd a térképen',
                                'aria-pressed' => 'false',
                                'data-toggle' => 'tooltip',
                                'data-map-program' => Url::to(['/sky-map/program', 'key' => $category['key']]),
                                'data-name' => $category['name'],
                            ]) ?>
                        <?php } ?>
                    </h3>

                    <?php if ($isOwn) { ?>
                        <?= $this->render('//_common-items/_badgeProgress', ['category' => $category]) ?>
                    <?php } ?>

                    <div class="badges__list">
                        <?php foreach ($category['levels'] as $level) { ?>
                            <?= $this->render('//_common-items/_badge', ['category' => $category, 'level' => $level]) ?>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
<?php } ?>
