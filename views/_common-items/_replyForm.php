<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $formId string */
/** @var $replyForm \app\models\forms\CommentForm */

// there are multiple forms on the page, the ids have to differ
$form = ActiveForm::begin(['id' => $formId, 'action' => ['observe/comment'], 'options' => ['class' => 'reply-form', 'hidden' => true]]); ?>

    <?= $form->field($replyForm, 'comment')->textarea(['id' => "$formId-comment"])->label('Válasz') ?>

    <?= Html::activeHiddenInput($replyForm, 'observation_id', ['id' => "$formId-observation_id"]) ?>
    <?= Html::activeHiddenInput($replyForm, 'parent_id', ['id' => "$formId-parent_id"]) ?>

    <?= Html::submitButton('Válasz küldése', ['class' => 'btn btn-primary']) ?>
    <?= Html::button('Mégse', ['class' => 'btn btn-outline', 'data-reply-cancel' => true]) ?>

<?php ActiveForm::end(); ?>
