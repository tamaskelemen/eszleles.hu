<?php

use app\components\SkyPrograms;

/* @var $this yii\web\View */
/* @var $key string */
/* @var $totals array */

$formatter = Yii::$app->formatter;
$hasPart = $key !== SkyPrograms::MESSIER && $totals['part'] < $totals['total'];
?>
<p class="badges__count">
    <b><?= $formatter->asInteger($totals['total']) ?> objektum</b>
    <?php if ($hasPart) { ?>
        <span>ebből <?= $formatter->asInteger($totals['part']) ?> látható Magyarországról</span>
    <?php } ?>
</p>
