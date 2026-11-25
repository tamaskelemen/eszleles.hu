<?php

use app\components\SkyPrograms;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $programs array */
/* @var $totals array */
/* @var $progress array badge categories of the visitor indexed by program, empty for guests */

$this->title = 'Észlelőprogramok';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="skymap-lead">
        Mélyég-objektumok listái, amelyeket érdemes végigészlelni. Az számít késznek, amelyikről van azonosított észlelésed.
        Minden szintért jelvény jár a profilodon, az előrehaladásodat csak te látod.
    </p>

    <p class="skymap-credit"><?= SkyPrograms::getVisibilityNote() ?></p>

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
</div>
