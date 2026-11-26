<?php
/* @var $this yii\web\View */
/* @var $filter app\components\recommender\Filter */
/* @var $value string[] */
/* @var $catalogs array label, description, count indexed by prefix */

echo $this->render('_checklist', [
    'filter' => $filter,
    'value' => $value,
    'items' => array_map(function ($catalog) {
        return [
            'label' => $catalog['label'],
            'title' => ($catalog['description'] ? $catalog['description'] . ', ' : '') . $catalog['count'] . ' objektum',
        ];
    }, $catalogs),
    'hint' => 'Ha egyet sem jelölsz, bármelyikből jöhet.',
]);
