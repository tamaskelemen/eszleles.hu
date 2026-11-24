<?php

use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div class="observe-index container">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_typeNav') ?>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '//_common-items/_listItem',
        'itemOptions' => ['class' => 'obs-cell'],
        'layout' => "{summary}\n<div class=\"obs-grid\">{items}</div>\n{pager}",
        'summary' => '<b>{totalCount, number}</b> észlelés, {begin, number}–{end, number} látható',
        'emptyText' => 'Ebben a kategóriában még nincs észlelés.',
    ]) ?>
</div>
