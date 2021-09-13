<?php

use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ObserveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Nap észlelések';
$this->params['breadcrumbs'][] = $this->title;

if (!Yii::$app->user->isGuest) {
    $isAdmin = Yii::$app->user->identity->isAdmin();
    $template = '{view} {update}' . ($isAdmin ? '{delete}' : '');

    $columns[] = [
        'class' => 'yii\grid\ActionColumn',
        'template' => $template
    ];

}

?>
<div class="observe-index">
    <div class="text-center">
        <h1>
            <?= Html::encode($this->title) ?>
        </h1>
    </div>
    <!--    --><?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="container">
        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemView' => '../_common-items/_listItem'
        ])
        ?>
    </div>
</div>
