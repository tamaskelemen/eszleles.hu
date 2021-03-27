<?php

use yii\helpers\Html;
use kartik\date\DatePicker;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ObserveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Hold észlelések';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="observe-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <!--    --><?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'itemView' => '../_common-items/_listItem'
    ])

    ?>

</div>
