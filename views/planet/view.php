<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Observe */

$this->title = $model->object_name;
$this->params['breadcrumbs'][] = ['label' => 'Bolygó észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$image = $model->getImage()->one();
?>
<div class="observe-view">

    <h1><?= Html::encode($this->title) ?></h1>


    <?php
    if ($model->observer_id === Yii::$app->user->id) {
        ?>
        <p>
            <?= Html::a('Módosítás', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        </p>
    <?php } ?>

    <?php
            if ($image !== null) { ?>
                <div class="img-container">
                    <?= Html::img("/" .$image->path, ['alt' => $model->object_name, 'class'=> 'img-fluid img-view m-auto']) ?>
                </div>
            <?php }

    ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'object_name',
            'catalog_number',
            'constellation',
            'object_type',
            'telescope',
            'mechanics',
            'camera',
            'seeing',
            'transparency',
            'location',
            'date',
            'source',
            'observer_id',
            'description:ntext',
            'uploaded_at',
            'edited_at',
        ],
    ]) ?>

</div>
