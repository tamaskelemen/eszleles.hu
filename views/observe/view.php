<?php

use yii\helpers\Html;
use app\models\observations\Deepsky;

/* @var $this yii\web\View */
/* @var $image \app\models\Image */
/* @var $model app\models\observations\Deepsky */
/* @var $commentForm app\models\forms\CommentForm */
/* @var $commentData \yii\data\ActiveDataProvider */

$this->registerJsFile('/js/image-tag-admin.js', ['depends' => 'app\assets\AppAsset']);

$this->title = $model->object_name;
$this->params['breadcrumbs'][] = ['label' => ucfirst(\app\models\Observe::getTypeName($model->type)) . ' észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$image = $model->getImage()->one();
$user = $model->getObserver()->one();

?>
<div class="observe-view container">
    <div class="row">
        <div class="col-lg-9 col-12">
            <?php
            if ($image !== null) { ?>
                <div id="img-container" class="img-container">
                    <?= Html::img($image->path, ['alt' => $model->object_name, 'class'=> 'img-fluid img-view m-auto', 'id' => $image->id]) ?>
                </div>

                <div id="tagbox">
                </div>

                <div id="taglist">

                </div>
                <?php
            }
            ?>
        </div>

        <div class="col-lg-3 col-12">
            <?php
            if ($model->observer_id === Yii::$app->user->id) {
                ?>
                <p>
                    <?= Html::a('Észlelés módosítása', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                </p>
            <?php } ?>
            <?= $this->render('_observer', ['user' => $user]) ?>
        </div>
    </div>

    <div class="details mt-3">
        <h2><?= $model->object_name?></h2>

        <p class="mb-4 mt-4"><?= Html::encode($model->description) ?></p>

        <div class="row">
            <?php foreach (Deepsky::getVisibleAttributes() as $attribute) {
                if ($model->$attribute) {
                    ?>
                    <div class="col-12 col-lg-6 mb-3">
                        <b class="mb-2 text-white"><?= $model->getAttributeLabel($attribute) ?> </b>
                        <br>
                        <?= $model->$attribute ?>
                    </div>
                <?php }
            } ?>
        </div>
    </div>

    <?= $this->render('../_common-items/_comments',[
        'commentData' => $commentData,
        'commentForm' => $commentForm,
    ])?>

</div>
