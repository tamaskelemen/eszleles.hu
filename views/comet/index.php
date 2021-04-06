<?php

use yii\helpers\Html;

use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ObserveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Üstökösök';
$this->params['breadcrumbs'][] = $this->title;

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
