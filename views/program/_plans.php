<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $plans app\models\ObservingPlan[] */
/* @var $totals int[] number of objects indexed by plan id */
/* @var $done int[] number of objects the visitor has observed indexed by plan id, empty for guests */
/* @var $showsOwner bool */

$formatter = Yii::$app->formatter;
$isGuest = Yii::$app->user->isGuest;
?>
<div class="badges__grid plans__grid">
    <?php foreach ($plans as $plan) {
        $total = $totals[$plan->id] ?? 0;
        ?>
        <div class="badges__card">
            <h4 class="h5"><?= Html::a(Html::encode($plan->name), ['/plan/view', 'id' => $plan->id]) ?></h4>

            <p class="badges__count">
                <?php if ($isGuest) { ?>
                    <b><?= $formatter->asInteger($total) ?> objektum</b>
                <?php } else { ?>
                    <b><?= $formatter->asInteger($done[$plan->id] ?? 0) ?> / <?= $formatter->asInteger($total) ?> objektum megvan</b>
                <?php } ?>
            </p>

            <p class="badges__text">
                <?php if ($showsOwner) { ?>
                    <?= Html::encode($plan->user->name) ?> összeállítása
                <?php } else { ?>
                    <?= $plan->is_public ? 'Nyilvános' : 'Csak te látod' ?>
                <?php } ?>
            </p>
        </div>
    <?php } ?>
</div>
