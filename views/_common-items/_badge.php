<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $category array see Badges::build() */
/* @var $level array */

$state = $level['isEarned'] ? 'badge-mark--' . $level['tier'] : 'badge-mark--locked';
$date = $level['isEarned'] && $level['date'] ? ', ' . substr($level['date'], 0, 10) : '';
$title = $level['title'] . ($level['isEarned'] ? $date : ' (még nincs meg)');
?>
<span class="badge-mark <?= $state ?>" title="<?= Html::encode($title) ?>" data-toggle="tooltip" tabindex="0" role="img" aria-label="<?= Html::encode($title) ?>"
      data-template='<div class="tooltip tooltip--text" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
>
    <?= $this->render('_badgeIcon', ['category' => $category, 'threshold' => $level['threshold']]) ?>
</span>
