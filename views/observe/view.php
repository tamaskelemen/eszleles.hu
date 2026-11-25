<?php

use yii\helpers\Html;
use app\models\Observe;
use app\models\observations\Deepsky;

/* @var $this yii\web\View */
/* @var $image \app\models\Image */
/* @var $model app\models\observations\Deepsky */
/* @var $commentForm app\models\forms\CommentForm */
/* @var $commentData \yii\data\ActiveDataProvider */

$this->title = $model->object_name;
$this->params['breadcrumbs'][] = ['label' => ucfirst(\app\models\Observe::getTypeName($model->type)) . ' észlelések', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerMetaTag([
        'name' => 'og:image',
        'content' => $model->getImagePath() ?? "/brand-logo.png"
]);

$user = $model->getObserver()->one();

?>
<article class="observe-view container">
    <header class="obs-head">
        <div class="obs-card__type">
            <?= $this->render('../_common-items/_typeIcon', ['type' => $model->type]) ?>
            <?= Observe::getTypeName($model->type) ?>
            <time><?= Html::encode($model->date) ?></time>
        </div>
        <h1><?= Html::encode($model->object_name) ?></h1>
        <?php
        if ($model->observer_id === Yii::$app->user->id) {
            ?>
            <?= Html::a('Észlelés módosítása', ['update', 'id' => $model->id], ['class' => 'btn btn-outline']) ?>
        <?php } ?>
        <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin()) { ?>
            <?= Html::a('Azonosítás szerkesztése', ['/admin/identify', 'id' => $model->id], ['class' => 'btn btn-outline']) ?>
        <?php } ?>
    </header>

    <?= $this->render('gallery.php', ['model' => $model]) ?>

    <div class="obs-body">
        <section class="obs-text">
            <h2><?= $model->getAttributeLabel('description') ?></h2>
            <p><?= nl2br(Html::encode($model->description)) ?></p>
        </section>

        <aside class="log-sheet">
            <?= $this->render('_observer', ['user' => $user]) ?>

            <dl>
                <?php if ($model->visibleSkyObjectLinks) { ?>
                    <div class="log-sheet__wide">
                        <dt>Azonosított objektum</dt>
                        <dd>
                            <ul class="sky-tags">
                                <?php foreach ($model->visibleSkyObjectLinks as $link) { ?>
                                    <li>
                                        <?= Html::a(Html::encode($link->getLabel()), $link->skyObject->getMapUrl(), [
                                            'class' => 'sky-tag',
                                            'title' => 'Megnyitás az égtérképen',
                                        ]) ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        </dd>
                    </div>
                <?php } ?>

                <?php foreach (Deepsky::getVisibleAttributes() as $attribute) {
                    if ($model->$attribute) {
                        $helpText = $model->getAttribteHelpText($attribute);
                        ?>
                        <div>
                            <dt>
                                <?= $model->getAttributeLabel($attribute) ?>
                                <?php if ($helpText) { ?>
                                    <i class="fa fa-info-circle" aria-hidden="true"
                                       data-toggle="popover"
                                       data-content="<?= $helpText ?>"
                                    ></i>
                                <?php } ?>
                            </dt>
                            <dd><?= Html::encode($model->$attribute) ?></dd>
                        </div>
                    <?php }
                } ?>
            </dl>

            <p class="log-sheet__uploaded">
                <?= $model->getAttributeLabel('uploaded_at') ?>:
                <?= Yii::$app->formatter->asTimeAgo($model->uploaded_at) ?>
            </p>
        </aside>

        <section class="obs-comments">
            <?= $this->render('../_common-items/_comments',[
                'commentData' => $commentData,
                'commentForm' => $commentForm,
            ])?>
        </section>
    </div>
</article>
