<?php

use app\models\Image;
use app\models\Observe;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model Observe|app\models\forms\AbstractObserveForm */
/* @var $form yii\widgets\ActiveForm */

$hasImage = $model instanceof Observe && $model->image !== null;
?>

<?php if ($hasImage) { ?>
    <div class="img-container mb-3">
        <?= Html::img($model->image->path, ['alt' => $model->object_name, 'class'=> 'img-fluid img-view m-auto']) ?>
    </div>
<?php } ?>

<?php
$field = $form->field($model, 'image')->fileInput([
    "class" => "form-control-file",
]);
if ($hasImage) {
    $field->label('Új képverzió feltöltése');
}
echo $field;
?>
<ul class="upload-description">
    <?php if ($hasImage) { ?>
        <li>
            Az új kép lesz az észlelés fő képe, a korábbi képek az észlelés oldalán továbbra is megtekinthetők.
        </li>
    <?php } ?>

    <li>
        A feltöltött kép mérete maximum <?= Image::getUploadSizeLimit() ?>Mb lehet
    </li>

    <li>
        Támogatott kiterjesztések: <?= Image::SUPPORTED_EXTENSIONS ?>
    </li>
</ul>
