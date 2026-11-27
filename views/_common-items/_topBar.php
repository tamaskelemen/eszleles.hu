<?php
/**
 * The thin strip above the navbar: today's date, and the sunset, sunrise and astronomical darkness of the night in
 * progress, or the coming one.
 *
 * @var $this yii\web\View
 * @var $night array of Astro::night()
 * @var $placeName string
 */

use app\assets\AppAsset;
use yii\helpers\Html;

$this->registerJsFile('/js/top-bar.js?v=' . filemtime(Yii::getAlias('@webroot/js/top-bar.js')), [
    'depends' => [AppAsset::class],
]);

$formatter = Yii::$app->formatter;
$now = time();

$icons = [
    // half Sun on the horizon, the arrow shows the direction
    'sunset' => '<path d="M7 16a5 5 0 0 1 10 0M3 16h18M5.6 10.6l1.1 1.1M18.4 10.6l-1.1 1.1M12 3v6M9.5 6.5 12 9l2.5-2.5"/>',
    'sunrise' => '<path d="M7 16a5 5 0 0 1 10 0M3 16h18M5.6 10.6l1.1 1.1M18.4 10.6l-1.1 1.1M12 9V3M9.5 5.5 12 3l2.5 2.5"/>',
    // crescent Moon, the arrow shows whether the darkness begins or ends
    'darkBegin' => '<path d="M15 4.5a7 7 0 1 0 4.5 12.3A6 6 0 0 1 15 4.5zM20 3v6M17.8 6.8 20 9l2.2-2.2"/>',
    'darkEnd' => '<path d="M15 4.5a7 7 0 1 0 4.5 12.3A6 6 0 0 1 15 4.5zM20 9V3M17.8 5.2 20 3l2.2 2.2"/>',
];
$labels = [
    'sunset' => 'Napnyugta',
    'sunrise' => 'Napkelte',
    'darkBegin' => 'Csillagászati sötétség kezdete',
    'darkEnd' => 'Csillagászati sötétség vége',
];
?>
<div class="top-bar">
    <div class="container top-bar__inner">
        <time class="top-bar__date" datetime="<?= date('Y-m-d', $now) ?>">
            <span class="top-bar__date-long"><?= $formatter->asDate($now, 'y. MMMM d. EEEE') ?></span>
            <span class="top-bar__date-short"><?= $formatter->asDate($now, 'MMM d.') ?></span>
        </time>

        <ul class="sun-times" aria-label="<?= Html::encode("Az éjszaka adatai, $placeName") ?>">
            <li class="sun-times__place"><?= Html::encode($placeName) ?></li>
            <?php foreach ($labels as $key => $label) {
                $time = $night[$key] === null ? null : date('H:i', $night[$key]);
                $tip = $time === null ? "Ezen az éjszakán nincs csillagászati sötétség" : $label;
            ?>
                <li class="sun-times__item">
                    <button class="sun-times__button" type="button" aria-expanded="false">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icons[$key] ?></svg>
                        <span class="sr-only"><?= $label ?>:</span>
                        <time <?= $time === null ? '' : 'datetime="' . date('c', $night[$key]) . '"' ?>><?= $time ?? '–' ?></time>
                    </button>
                    <span class="sun-times__tip" aria-hidden="true"><?= Html::encode($tip) ?> <span class="sun-times__tip-place">(<?= Html::encode($placeName) ?>)</span></span>
                </li>
            <?php } ?>
        </ul>
    </div>
</div>
