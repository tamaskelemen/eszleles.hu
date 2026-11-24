<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $commentForm \app\models\forms\CommentForm */
?>
<div class="comment">
    <?php $form = ActiveForm::begin(['action' =>['observe/comment']]); ?>

    <?= $form->field($commentForm, 'comment')->textarea() ?>

    <?= $form->field($commentForm, 'observation_id')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('Mentés', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>