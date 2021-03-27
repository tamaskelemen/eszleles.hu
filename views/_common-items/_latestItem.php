<?php
/** @var $model \app\models\Observe */

use yii\helpers\Html;

?>
<div class="col-lg-3 list-item">
    
    <img src="<?= $model->getThumbnailPath() ?>" alt="">
    <h3><?= $model->object_name ?></h3>

    <p><?= Html::encode($model->description) ?></p>

    <p><a class="btn btn-default" href="http://www.yiiframework.com/doc/">Yii Documentation &raquo;</a></p>
</div>
