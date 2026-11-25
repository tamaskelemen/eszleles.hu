<?php
namespace app\commands;

use app\components\BadgeSync;
use app\models\User;
use app\models\UserBadge;
use yii\console\Controller;
use yii\console\ExitCode;

class BadgesController extends Controller
{
    /**
     * Counts the badges of every user again. It can be run any time.
     */
    public function actionSync()
    {
        foreach (User::find()->select('id')->column() as $id) {
            BadgeSync::sync($id);
        }

        $this->stdout(UserBadge::find()->count() . ' badges of ' . UserBadge::find()->count('DISTINCT user_id') . " users.\n");

        return ExitCode::OK;
    }
}
