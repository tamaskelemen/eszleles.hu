<?php
namespace app\controllers;


use app\models\BugReport;
use app\models\BugReportSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (
            Yii::$app->user->isGuest
            || !Yii::$app->user->getIdentity()->is_admin
        ) {
            throw new NotFoundHttpException("A keresett oldal nem található");
        }

        $this->layout = 'mainAdmin';
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return $this->render("index");
    }

}