<?php

use yii\helpers\Html;
use app\models\observations\Deepsky;

/* @var $this yii\web\View */
/* @var $image \app\models\Image */
/* @var $model app\models\observations\Deepsky */
/* @var $commentForm app\models\forms\CommentForm */
/* @var $commentData \yii\data\ActiveDataProvider */

$this->title = $model->object_name;
$this->params['breadcrumbs'][] = ['label' => ucfirst(\app\models\Observe::getTypeName($model->type)) . ' észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$user = $model->getObserver()->one();

?>
<div class="observe-view container">
    <h2 class="mb-3"><?= $model->object_name?></h2>

    <div class="row">
        <div class="col-lg-9 col-12">
            <?= $this->render('gallery.php', ['model' => $model]) ?>
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
        <b class="mb-2 text-white"><?= $model->getAttributeLabel('description') ?> </b>
        <p class="mb-4"><?= Html::encode($model->description) ?></p>

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
