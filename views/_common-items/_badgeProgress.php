<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $category array see Badges::build() */

$formatter = Yii::$app->formatter;
$next = $category['next'];

if ($category['part'] !== null) {
    $main = [$category['partCount'], $category['part'], $category['partNote']];
    $rest = [$category['count'], $category['total'], 'összesen'];
} else {
    $main = [$category['count'], $category['total'], null];
    $rest = null;
}

$write = function (array $numbers) use ($formatter) {
    return $formatter->asInteger($numbers[0])
        . ($numbers[1] === null ? '' : ' / ' . $formatter->asInteger($numbers[1]))
        . ($numbers[2] === null ? '' : ' ' . $numbers[2]);
};
?>
<p class="badges__count">
    <b><?= $write($main) ?></b>
    <?php if ($rest !== null) { ?>
        <span><?= $write($rest) ?></span>
    <?php } ?>
</p>

<?php if ($next !== null) { ?>
    <div class="badges__bar" role="progressbar" aria-label="<?= Html::encode('Következő: ' . $next['title']) ?>"
         aria-valuemin="0" aria-valuemax="<?= $next['threshold'] ?>" aria-valuenow="<?= $next['value'] ?>">
        <span style="width: <?= round(100 * $next['value'] / $next['threshold'], 1) ?>%"></span>
    </div>
    <p class="badges__next">
        Következő: <?= Html::encode($next['title']) ?>, még <?= $formatter->asInteger($next['threshold'] - $next['value']) ?>
    </p>
<?php } else { ?>
    <p class="badges__next">Minden szint megvan.</p>
<?php } ?>
