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
    <div class="observer__name">
        <?= Html::a(
            $this->render('//_common-items/_avatar', ['user' => $user, 'size' => 'small']),
            Url::toRoute(["/user/profile", "id" => $user->id]),
            ['tabindex' => -1, 'aria-hidden' => 'true']
        ) ?>
        <div>
            <span class="observer__label">Észlelő</span>
            <?= Html::a(Html::encode($user->name), Url::toRoute(["/user/profile", "id" => $user->id])) ?>
        </div>
    </div>

    <?= $this->render('//_common-items/_social-links', ['user' => $user]) ?>

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