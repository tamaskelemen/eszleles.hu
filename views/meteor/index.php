<?php

/* @var $this yii\web\View */
/* @var $searchModel app\models\ObserveSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Meteor észlelések';
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('//_common-items/_listing', ['dataProvider' => $dataProvider]);
