<?php
namespace app\controllers;


use app\models\ActivitySearch;
use app\models\User;
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

    /**
     * Lists the activities of every user.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ActivitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists the activities of a user.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUser(int $id)
    {
        $user = User::findOne($id);

        if ($user === null) {
            throw new NotFoundHttpException("A keresett oldal nem található");
        }

        $searchModel = new ActivitySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $user->id);

        return $this->render('user', [
            'user' => $user,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}