<?php

use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $plans app\models\ObservingPlan[] */
/* @var $totals int[] number of objects indexed by plan id */
/* @var $done int[] number of objects observed indexed by plan id, by the visitor or by the user of the profile */
/* @var $showsOwner bool */
/* @var $showsProgress bool|null whether $done is shown, by default for the logged in visitors */
/* @var $showsMap bool|null whether there is a sky map on the page to show the plans on */

$formatter = Yii::$app->formatter;
$showsProgress = $showsProgress ?? !Yii::$app->user->isGuest;
$showsMap = $showsMap ?? false;
// a plan description can be long, the built-in ones are a few sentences
$descriptionLength = 300;
?>
<div class="badges__grid plans__grid">
    <?php foreach ($plans as $plan) {
        $total = $totals[$plan->id] ?? 0;
        ?>
        <div class="badges__card">
            <h4 class="h5 badges__title">
                <?= Html::a(Html::encode($plan->name), ['/plan/view', 'id' => $plan->id]) ?>
                <span class="plans__icons">
                    <?= $this->render('//plan/_copyCount', ['plan' => $plan]) ?>
                    <?php if ($showsMap && $total > 0) { ?>
                        <?= Html::button('<i class="fa fa-globe" aria-hidden="true"></i>', [
                            'class' => 'info-tip badges__map',
                            'title' => 'Mutasd a térképen',
                            'aria-label' => $plan->name . ': Mutasd a térképen',
                            'aria-pressed' => 'false',
                            'data-toggle' => 'tooltip',
                            'data-map-program' => Url::to(['/sky-map/plan', 'id' => $plan->id]),
                            'data-name' => $plan->name,
                        ]) ?>
                    <?php } ?>
                </span>
            </h4>

            <p class="badges__count">
                <?php if (!$showsProgress) { ?>
                    <b><?= $formatter->asInteger($total) ?> objektum</b>
                <?php } else { ?>
                    <b><?= $formatter->asInteger($done[$plan->id] ?? 0) ?> / <?= $formatter->asInteger($total) ?> objektum megvan</b>
                <?php } ?>
            </p>

            <?php if ($plan->description) { ?>
                <p class="badges__text"><?= nl2br(Html::encode(StringHelper::truncate($plan->description, $descriptionLength))) ?></p>
            <?php } ?>

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
