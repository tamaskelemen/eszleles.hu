<?php

use app\components\feed\RelatedObservationsSource;
use app\components\related\RelatedObservations;
use app\models\Observe;
use app\widgets\InfiniteList;

/* @var $this yii\web\View */
/* @var $model Observe */

if (!RelatedObservations::count($model)) {
    return;
}
?>
<section class="obs-related">
    <h2>Ez is érdekelhet</h2>
    <?= InfiniteList::widget([
        'source' => new RelatedObservationsSource($model),
        'url' => ['/observe/related', 'id' => $model->id],
        'first' => RelatedObservations::BATCH,
        'maxLoads' => RelatedObservations::TOTAL / RelatedObservations::BATCH - 1,
    ]) ?>
</section>
