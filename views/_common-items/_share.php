<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $url string absolute url of the shared page */
/* @var $title string */
/* @var $text string a short line shown with the link in the share sheet and the e-mail */
/* @var $label string|null text of the button */

$encodedUrl = rawurlencode($url);
$label = $label ?? 'Megosztás';
?>
<div class="share dropdown">
    <?= Html::button('<i class="fa fa-share-alt" aria-hidden="true"></i> ' . Html::encode($label), [
        'class' => 'share__toggle',
        'data-toggle' => 'dropdown',
        'data-share' => 1,
        'data-url' => $url,
        'data-title' => $title,
        'data-text' => $text,
        'aria-haspopup' => 'true',
        'aria-expanded' => 'false',
    ]) ?>

    <div class="dropdown-menu dropdown-menu-right">
        <?= Html::button('<i class="fa fa-link fa-fw" aria-hidden="true"></i> Link másolása', [
            'class' => 'dropdown-item',
            'data-share-copy' => $url,
        ]) ?>
        <?= Html::a(
            '<i class="fa fa-facebook-official fa-fw" aria-hidden="true"></i> Facebook',
            "https://www.facebook.com/sharer/sharer.php?u=$encodedUrl",
            ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener']
        ) ?>
        <?= Html::a(
            '<i class="fa fa-whatsapp fa-fw" aria-hidden="true"></i> WhatsApp',
            'https://wa.me/?text=' . rawurlencode("$text $url"),
            ['class' => 'dropdown-item', 'target' => '_blank', 'rel' => 'noopener']
        ) ?>
        <?= Html::a(
            '<i class="fa fa-envelope fa-fw" aria-hidden="true"></i> E-mail',
            'mailto:?subject=' . rawurlencode($title) . '&body=' . rawurlencode("$text\n\n$url"),
            ['class' => 'dropdown-item']
        ) ?>
    </div>
</div>
