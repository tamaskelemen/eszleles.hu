<?php

use app\components\SocialLogin;
use yii\helpers\Html;

/* @var $this yii\web\View */

$clients = SocialLogin::getClients();
?>
<?php if ($clients) { ?>
    <div class="social-login__or">vagy</div>
    <div class="social-login">
        <?php foreach ($clients as $id => $client) { ?>
            <?= Html::a(
                Html::img('/pictures/icons/' . $client->getViewOptions()['icon'], ['alt' => '']) . Html::encode($client->getViewOptions()['label']),
                ['/site/auth', 'authclient' => $id],
                ['class' => "btn btn-{$id}", 'rel' => 'nofollow']
            ) ?>
        <?php } ?>
    </div>
<?php } ?>
