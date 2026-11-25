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
                '<i class="fa fa-' . $client->getViewOptions()['icon'] . '" aria-hidden="true"></i> ' . Html::encode($client->getViewOptions()['label']),
                ['/site/auth', 'authclient' => $id],
                ['class' => 'btn btn-outline', 'rel' => 'nofollow']
            ) ?>
        <?php } ?>
    </div>
<?php } ?>
