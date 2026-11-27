<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $events string[] labels by event */
/* @var $daily array rows of Stats::getDaily() */
/* @var $last30 array Stats::getTotals() */
/* @var $total array Stats::getTotals() */

$this->title = 'Ajánló használata';

$cell = function (array $counts) {
    $sum = $counts['guest'] + $counts['user'];

    return $sum === 0
        ? '<span class="text-muted">0</span>'
        : '<strong>' . $sum . '</strong><br><small class="text-muted">' . $counts['user'] . ' tag / ' . $counts['guest'] . ' vendég</small>';
};
?>
<div class="admin-recommender container pt-4">
    <h1><?= Html::encode($this->title) ?></h1>

    <p class="text-muted">
        A „Mit észleljek most?” ajánló kéréseinek napi száma (magyar idő szerint). A megnyitás a főoldal betöltésekor
        kért első ajánlat; a tagok száma a különböző bejelentkezett felhasználóké, akik aznap használták.
    </p>

    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
            <tr>
                <th>Nap</th>
                <?php foreach ($events as $label) { ?>
                    <th class="text-right"><?= Html::encode($label) ?></th>
                <?php } ?>
                <th class="text-right">Tagok</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (['Összesen' => $total, 'Utolsó 30 nap' => $last30] as $label => $row) { ?>
                <tr class="table-summary">
                    <th><?= $label ?></th>
                    <?php foreach (array_keys($events) as $event) { ?>
                        <td class="text-right"><?= $cell($row['events'][$event]) ?></td>
                    <?php } ?>
                    <td class="text-right"><strong><?= $row['users'] ?></strong></td>
                </tr>
            <?php } ?>
            <?php foreach ($daily as $row) { ?>
                <tr>
                    <td><?= Yii::$app->formatter->asDate($row['day'], 'php:Y.m.d. (D)') ?></td>
                    <?php foreach (array_keys($events) as $event) { ?>
                        <td class="text-right"><?= $cell($row['events'][$event]) ?></td>
                    <?php } ?>
                    <td class="text-right"><?= $row['users'] ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
