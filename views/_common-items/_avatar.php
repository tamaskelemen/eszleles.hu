<?php

use yii\helpers\Html;

/**
 * @var $user \app\models\User
 * @var $size string|null modifier class, e.g. "small"
 */

$class = 'avatar' . (empty($size) ? '' : " avatar--$size");
?>
<?= Html::img($user->getAvatarUrl(), [
    'class' => $class,
    'alt' => "{$user->name} profilképe",
    'loading' => 'lazy',
]) ?>
