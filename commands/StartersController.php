<?php

namespace app\commands;

use app\components\Email;
use app\models\User;
use yii\console\Controller;

class StartersController extends Controller
{
    public function actionIndex()
    {
        /** @var User[] $users */
        $users = User::find()->all();

        foreach ($users as $user) {
            if (empty($user->email)) {
                continue;
            }

            $result = Email::send(
                $user->email,
                "Elindult az eszleles.hu!",
                "starters"
            );

            echo $user->email . PHP_EOL;
            if (!$result) {
                var_dump("geci");
            }
        }
    }
}