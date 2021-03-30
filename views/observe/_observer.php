<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $user \app\models\User
 */

$websites = $user->getSocialLinks();

?>
<div>
    <h4 class="mt-2">Észlelő:</h4>
    <?= Html::a($user->name, Url::toRoute(["/profil", "id" => $user->id])) ?>
<!--    <p>-->
<!--        --><?php //foreach ($websites as $type => $website) {
//            if (!empty($website)) { ?>
<!--                <a href="--><?//= $website?><!--">-->
                    <?php // Html::img("/pictures/{$type}.png", ['class'=> "social-icon"])?>
<!--                </a>-->
<!--                --><?php
//            }
//        }
//        ?>
<!--    </p>-->
</div>