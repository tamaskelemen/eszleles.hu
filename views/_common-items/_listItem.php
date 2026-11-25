<?php

use app\models\Observe;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var $this yii\web\View */
/** @var $model Observe */

$commentCount = count($model->comments);
?>
<a class="obs-card" href="<?= Url::toRoute(['/' . $model->type . '/view', 'id' => $model->id]) ?>">
    <div class="obs-card__img">
        <img src="<?= $model->getThumbnailPath() ?>" alt="" width="300" height="200" loading="lazy">
        <?php if ($model->isNew()) { ?>
            <span class="obs-card__new">Új feltöltés</span>
        <?php } ?>
    </div>

    <div class="obs-card__body">
        <div class="obs-card__type">
            <?= $this->render('_typeIcon', ['type' => $model->type]) ?>
            <?= Observe::getTypeName($model->type) ?>
            <time><?= Html::encode($model->date) ?></time>
        </div>

        <h3><?= Html::encode($model->object_name) ?></h3>

        <p><?= Html::encode($model->description) ?></p>

        <div class="obs-card__meta">
            <span><?= Html::encode($model->observer->name) ?></span>
            <?php if ($commentCount > 0) { ?>
                <span><?= $commentCount ?> hozzászólás</span>
            <?php } ?>
        </div>
    </div>
</a>
