<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $user \app\models\User
 */

?>
<div>
    <h4 class="mt-2">Észlelő:</h4>
    <?= Html::a($user->name, Url::toRoute(["/user/profile", "id" => $user->id])) ?>

    <p class="mt-1">
        <?php foreach ($user->getSocialLinks() as $site => $link) {
            if ($link) { ?>
                <?= Html::a(
                    Html::img("/pictures/icons/$site.svg", ['alt'=>"$site logo"]),
                    $link
                ) ?>
            <?php }
        }
        ?>
    </p>
</div>