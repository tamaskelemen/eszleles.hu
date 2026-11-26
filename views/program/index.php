<?php

use app\components\SkyPrograms;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $programs array */
/* @var $totals array */
/* @var $progress array badge categories of the visitor indexed by program, empty for guests */
/* @var $showsPlans bool whether the observing plans are switched on, see Features */
/* @var $ownPlans app\models\ObservingPlan[] */
/* @var $publicPlans app\models\ObservingPlan[] public plans of the others */
/* @var $planTotals int[] number of objects indexed by plan id */
/* @var $planDone int[] number of objects the visitor has observed indexed by plan id */

$this->title = 'Észlelőprogramok';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="skymap-lead">
        Mélyég-objektumok listái, amelyeket érdemes végigészlelni. Ötletet adhatnak, hogy milyen sorrendben fedezd fel az égboltot.
        <?= $this->render('//_common-items/_infoTip', [
            'label' => 'Mi számít késznek?',
            'text' => 'Az számít késznek, amelyikről van azonosított észlelésed. Minden szintért jelvény jár a profilodon, az előrehaladásodat csak te látod.',
        ]) ?>
    </p>

    <p class="skymap-credit"><?= SkyPrograms::getVisibilityNote() ?></p>

    <?php if (Yii::$app->user->isGuest) { ?>
        <?= $this->render('_guestCta') ?>
    <?php } ?>

    <div class="badges__grid">
        <?php foreach (array_intersect_key($programs, $totals) as $key => $program) { ?>
            <div class="badges__card">
                <h2 class="h3"><?= Html::a(Html::encode($program['name']), ['view', 'key' => $key]) ?></h2>

                <?php if (isset($progress[$key])) { ?>
                    <?= $this->render('//_common-items/_badgeProgress', ['category' => $progress[$key]]) ?>
                <?php } else { ?>
                    <?= $this->render('_totals', ['key' => $key, 'totals' => $totals[$key]]) ?>
                <?php } ?>

                <p class="badges__text"><?= Html::encode($program['description']) ?></p>
            </div>
        <?php } ?>
    </div>

    <?php if ($showsPlans) { ?>
        <h2 class="plans__title">Észlelési tervek</h2>

        <p class="skymap-lead">
            Saját listák a megfigyelni kívánt objektumokból. Bárki összeállíthat egyet, és nyilvánossá teheti, hogy mások is lássák.
        </p>

        <?php if (!Yii::$app->user->isGuest) { ?>
            <h3 class="h4">Saját terveim</h3>

            <?php if (empty($ownPlans)) { ?>
                <p>Még nincs észlelési terved.</p>
            <?php } else { ?>
                <?= $this->render('_plans', ['plans' => $ownPlans, 'totals' => $planTotals, 'done' => $planDone, 'showsOwner' => false]) ?>
            <?php } ?>
        <?php } ?>

        <?php /* guests are sent to the login by PlanController, and come back to the form */ ?>
        <p><?= Html::a('<i class="fa fa-plus" aria-hidden="true"></i> Új észlelési terv', ['/plan/create'], ['class' => 'btn btn-primary']) ?></p>

        <?php if (!empty($publicPlans)) { ?>
            <h3 class="h4">Mások nyilvános tervei</h3>
            <?= $this->render('_plans', ['plans' => $publicPlans, 'totals' => $planTotals, 'done' => $planDone, 'showsOwner' => true]) ?>
        <?php } ?>
    <?php } ?>
</div>
