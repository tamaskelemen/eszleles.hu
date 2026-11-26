<?php

use app\models\IdentificationSearch;
use app\models\ObservationSkyObject;
use app\models\Observe;
use yii\grid\CheckboxColumn;
use app\widgets\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\IdentificationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $counts int[] */

$this->title = 'Azonosítás';

$isReview = $searchModel->filter === IdentificationSearch::FILTER_REVIEW;
$isNone = $searchModel->filter === IdentificationSearch::FILTER_NONE;
$columns = [];

if ($isReview || $isNone) {
    $columns[] = ['class' => CheckboxColumn::class, 'name' => 'ids'];
}

$columns = array_merge($columns, [
    [
        'label' => 'Kép',
        'format' => 'raw',
        'mobile' => GridView::MOBILE_MEDIA,
        'value' => function (Observe $model) {
            return $model->thumbnail ? Html::img($model->thumbnail->path, ['alt' => '', 'width' => 80, 'loading' => 'lazy']) : '';
        },
    ],
    [
        'attribute' => 'object_name',
        'label' => $searchModel->getAttributeLabel('object_name'),
        'format' => 'raw',
        'mobile' => GridView::MOBILE_TITLE,
        'filter' => Html::activeTextInput($searchModel, 'object_name', ['class' => 'form-control']),
        'value' => function (Observe $model) {
            return Html::a(Html::encode($model->object_name), $model->getViewUrl());
        },
    ],
    [
        'attribute' => 'constellation',
        'filter' => false,
        'enableSorting' => false,
        'label' => 'Csillagkép',
        'value' => function (Observe $model) {
            return (string)$model->constellation;
        },
    ],
    [
        'label' => $searchModel->getAttributeLabel('type'),
        'attribute' => 'type',
        'enableSorting' => false,
        'filter' => Observe::getAllTypes(),
        'mobile' => GridView::MOBILE_META,
        'value' => function (Observe $model) {
            return Observe::getTypeName($model->type);
        },
    ],
    [
        'label' => $searchModel->getAttributeLabel('observerName'),
        'attribute' => 'observerName',
        'enableSorting' => false,
        'mobile' => GridView::MOBILE_NOTE,
        'value' => function (Observe $model) {
            return $model->observer ? $model->observer->name : '';
        },
    ],
    [
        'label' => $searchModel->getAttributeLabel('skyObject'),
        'attribute' => 'skyObject',
        'enableSorting' => false,
        'format' => 'raw',
        'value' => function (Observe $model) {
            $lines = [];

            foreach ($model->skyObjectLinks as $link) {
                if ($link->status === ObservationSkyObject::STATUS_REJECTED) {
                    continue;
                }

                $note = ObservationSkyObject::getAllStatuses()[$link->status];
                if ($link->reason) {
                    $note .= ': ' . $link->reason;
                }

                $lines[] = Html::encode($link->getLabel()) . ' ' . Html::tag('small', Html::encode("($note)"), ['class' => 'hint-block']);
            }

            if ($model->has_no_sky_object) {
                $lines[] = Html::tag('small', 'nincs objektuma', ['class' => 'hint-block']);
            }

            return implode('<br>', $lines);
        },
    ],
    [
        'label' => '',
        'format' => 'raw',
        'mobile' => GridView::MOBILE_ACTIONS,
        'value' => function (Observe $model) {
            return Html::a('Szerkesztés', ['/admin/identify', 'id' => $model->id], ['class' => 'btn btn-outline btn-sm']);
        },
    ],
]);
?>
<div class="admin-identification container-fluid pt-4">
    <h1><?= Html::encode($this->title) ?></h1>

    <ul class="nav nav-tabs mb-3">
        <?php foreach (IdentificationSearch::getAllFilters() as $filter => $label) { ?>
            <li class="nav-item">
                <?= Html::a("$label ({$counts[$filter]})", ['/admin/identification', 'filter' => $filter], [
                    'class' => 'nav-link' . ($searchModel->filter === $filter ? ' active' : ''),
                ]) ?>
            </li>
        <?php } ?>
    </ul>

    <?= Html::beginForm(['/admin/identification-bulk'], 'post', ['id' => 'identification-bulk']) ?>
    <?php if ($isReview) { ?>
        <?= Html::submitButton('Kijelöltek jóváhagyása', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'confirm']) ?>
    <?php } else if ($isNone) { ?>
        <?= Html::submitButton('A kijelölteknek nincs objektuma', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'dismiss']) ?>
    <?php } ?>
    <?= Html::endForm() ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'filterUrl' => Url::to(['/admin/identification', 'filter' => $searchModel->filter]),
        'options' => ['class' => 'grid-view table-responsive mt-3'],
        'columns' => $columns,
    ]) ?>
</div>
<?php
// the checkboxes can not be inside of the form, because the filters of the grid have their own
$this->registerJs("$('#identification-bulk').on('submit', function () {
    $('input[name=\"ids[]\"]').attr('form', 'identification-bulk');
});");
