<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $user \app\models\User
 */

?>
<div class="observer">
    <div>
        <span class="observer__label">Észlelő</span>
        <?= Html::a(Html::encode($user->name), Url::toRoute(["/user/profile", "id" => $user->id])) ?>
    </div>

    <p class="social">
        <?php foreach ($user->getSocialLinks() as $site => $link) {
            if ($link) { ?>
                <?= Html::a(
                        Html::img(
                            "/pictures/icons/$site.svg",
                            [
                                'alt' => "$site logo",
                                'title' => ucfirst($site),
                            ]),
                        $link
                ) ?>
            <?php }
        }
        ?>
    </p>
</div>