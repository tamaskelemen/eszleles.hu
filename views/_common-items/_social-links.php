<?php

use yii\helpers\Html;

/**
 * The profile links of a user as a row of icons, nothing if none is filled.
 *
 * @var $user \app\models\User
 */

$links = $user->getSocialLinks();
?>
<?php if ($links) { ?>
    <p class="social">
        <?php foreach ($links as $attribute => $link) {
            $label = $user->getAttributeLabel($attribute);
            echo Html::a(
                Html::img("/pictures/icons/$attribute.svg", ['alt' => $label, 'width' => 24, 'height' => 24]),
                $link,
                [
                    // the website shows its address, the networks their name
                    'title' => $attribute === 'website' ? $link : $label,
                    'data-toggle' => 'tooltip',
                    'rel' => 'me nofollow noopener',
                    'target' => '_blank',
                ]
            );
        } ?>
    </p>
<?php } ?>
