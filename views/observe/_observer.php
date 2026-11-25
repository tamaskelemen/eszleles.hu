<?php

use app\components\Badges;
use app\models\UserBadge;
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

    <?php if ($user->badges) { ?>
        <div class="badges__list badges__list--small" aria-label="Legmagasabb jelvények" role="group">
            <?php foreach (UserBadge::filterHighest($user->badges) as $badge) { ?>
                <?= $this->render('//_common-items/_badge', [
                    'category' => Badges::getCategories()[$badge->category],
                    'level' => $badge->getLevel(),
                ]) ?>
            <?php } ?>
        </div>
    <?php } ?>
</div>