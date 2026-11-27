<?php

use app\components\related\RelatedObservations;
use app\models\Observe;

/* @var $this yii\web\View */
/* @var $model Observe */

$related = RelatedObservations::find($model);
if (!$related) {
    return;
}
?>
<section class="obs-related">
    <h2>Kapcsolódó észlelések</h2>
    <div class="obs-grid">
        <?php foreach ($related as $item) { ?>
            <div class="obs-cell">
                <?= $this->render('//_common-items/_listItem', [
                    'model' => $item['observation'],
                    'reasons' => $item['reasons'],
                ]) ?>
            </div>
        <?php } ?>
    </div>
</section>
