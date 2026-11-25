<?php

namespace app\controllers;

use app\models\Notification;
use Yii;
use yii\filters\AccessControl;
use yii\filters\AjaxFilter;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * NotificationController serves the notification box of the logged in user.
 */
class NotificationController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'ajax' => [
                'class' => AjaxFilter::class,
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'mark-all-seen' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists the notifications, latest first.
     * @param int|null $before if set, only notifications older than the one with this id are listed
     * @return \yii\web\Response
     */
    public function actionIndex(?int $before = null)
    {
        $notifications = Notification::find()
            ->ofUser(Yii::$app->user->id)
            ->andFilterWhere(['<', 'id', $before])
            ->with('comment.user', 'comment.observation.thumbnail', 'badge')
            ->orderBy(['id' => SORT_DESC])
            ->limit(Notification::PAGE_SIZE + 1)
            ->all();

        return $this->asJson([
            'html' => $this->renderPartial('_items', [
                'notifications' => array_slice($notifications, 0, Notification::PAGE_SIZE),
                'hasMore' => count($notifications) > Notification::PAGE_SIZE,
            ]),
            'unseenCount' => Notification::countUnseen(Yii::$app->user->id),
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionMarkAllSeen()
    {
        Notification::markSeen(Yii::$app->user->id);

        return $this->asJson(['unseenCount' => 0]);
    }
}
