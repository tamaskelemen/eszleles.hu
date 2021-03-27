<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\models\observations\Planet;

/* @var $this yii\web\View */
/* @var $model app\models\observations\Planet */

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
                    <?= Html::img($image->path, ['alt' => $model->object_name, 'class'=> 'img-fluid img-view m-auto']) ?>
                </div>
            <?php }

    ?>

    <div>
        <h2><?= $model->object_name?></h2>

        <p class="mb-4 mt-4"><?= Html::encode($model->description) ?></p>

        <div class="row">
            <?php foreach (Planet::getVisibleAttributes() as $attribute) {
                if ($model->$attribute) {
                    ?>
                    <div class="col-12 col-lg-6 mb-3">
                        <b class="mb-2"><?= $model->getAttributeLabel($attribute) ?> </b>
                        <br>
                        <?= $model->$attribute ?>
                    </div>
                <?php }
            } ?>
        </div>
    </div>

    <?php
//    DetailView::widget([
//        'model' => $model,
//        'attributes' => [
//            'id',
//            'object_name',
//            'catalog_number',
//            'constellation',
//            'object_type',
//            'telescope',
//            'mechanics',
//            'camera',
//            'seeing',
//            'transparency',
//            'location',
//            'date',
//            'source',
//            'observer_id',
//            'description:ntext',
//            'uploaded_at',
//            'edited_at',
//        ],
//    ]) ?>

</div>
