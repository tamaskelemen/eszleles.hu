<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $category array see Badges::build() */
/* @var $level array */

$icons = [
    'observations' => '<circle cx="12" cy="12" r="3"/><path d="M2.5 12C5 7.5 8.3 5.5 12 5.5s7 2 9.5 6.5c-2.5 4.5-5.8 6.5-9.5 6.5s-7-2-9.5-6.5z"/>',
    'comments' => '<path d="M4 5h16v11h-9l-4.5 3.5V16H4z"/>',
    'types' => '<path d="M12 3l2.6 5.8 6.4.7-4.8 4.2 1.4 6.3-5.6-3.2L6.4 20l1.4-6.3L3 9.5l6.4-.7z"/>',
    'constellations' => '<path d="M4 17l5-8 6 3 5-8"/><circle cx="4" cy="17" r="1.5"/><circle cx="9" cy="9" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="20" cy="4" r="1.5"/>',
];

$glyph = $category['glyph'];
$state = $level['isEarned'] ? 'badge-mark--' . $level['tier'] : 'badge-mark--locked';
$date = $level['isEarned'] && $level['date'] ? ', ' . substr($level['date'], 0, 10) : '';
$title = $level['title'] . ($level['isEarned'] ? $date : ' (még nincs meg)');
?>
<span class="badge-mark <?= $state ?>" title="<?= Html::encode($title) ?>" data-toggle="tooltip" tabindex="0" role="img" aria-label="<?= Html::encode($title) ?>"
      data-template='<div class="tooltip tooltip--text" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
>
    <svg viewBox="0 0 44 48" width="44" height="48" aria-hidden="true">
        <path class="badge-mark__shape" d="M22 1.5L42 12.5v23L22 46.5 2 35.5v-23z"/>
        <?php if ($glyph !== null) { ?>
            <text class="badge-mark__glyph" x="22" y="22" font-size="<?= strlen($glyph) > 3 ? 11 : 14 ?>"><?= Html::encode($glyph) ?></text>
        <?php } else { ?>
            <g class="badge-mark__icon" transform="translate(13.5 7.5) scale(.7)"><?= $icons[$category['key']] ?? '' ?></g>
        <?php } ?>
        <text class="badge-mark__number" x="22" y="36"><?= $level['threshold'] ?></text>
    </svg>
</span>
