<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $hasBadges bool|null whether badges are given for it, true by default */
?>
<div class="badges__card program-cta">
    <p>
        <b>Szeretnéd teljesíteni?</b>
        Lépj be, és követheted, melyik objektumot észlelted már és melyik hiányzik még<?= ($hasBadges ?? true)
            ? ', a teljesített szintekért pedig jelvényt kapsz a profilodon.'
            : ', és saját észlelési tervet is összeállíthatsz.' ?>
    </p>
    <?= Html::a('Belépés', ['/site/login'], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Regisztráció', ['/site/signup'], ['class' => 'btn btn-outline-secondary']) ?>
</div>
