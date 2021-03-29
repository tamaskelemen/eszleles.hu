<?php

use yii\helpers\Url;

/** @var $params array */
?>
<div>
    <b>Kedves Észlelő!</b>

    <p>
        Kattins <?= \yii\helpers\Html::a("ide", Url::toRoute(['/site/new-password', 'token' => $params['token']], true)) ?> új jelszavad beállításához.
    </p>
    <p>
        Üdvözlettel,<br>
        Az Észlelés.hu csapata
    </p>
</div>