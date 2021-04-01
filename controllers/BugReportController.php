<?php

namespace app\controllers;

use app\components\Flash;
use Yii;
use app\models\BugReport;
use app\models\BugReportSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BugReportController implements the CRUD actions for BugReport model.
 */
class BugReportController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'index'],
                'rules' => [
                    [
                        'actions' => ['create', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['index', 'view', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' =>  function ($rule, $action) {
                            if (!Yii::$app->user->identity->isAdmin()) {
                                Flash::addWarning('Ehhez a művelethez nincs elég jogosultságod.');
                                return $this->goBack();
                            }
                        }
                    ],
                ],

            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all BugReport models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->is_admin) {
            return $this->goHome();
        }
        $searchModel = new BugReportSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BugReport model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->is_admin) {
            return $this->goHome();
        }
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new BugReport model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {

        $model = new BugReport();

        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->getIdentity()->id;
            $model->status = BugReport::STATUS_OPEN;

             if ($model->save()) {
                 Flash::addSuccess("Sikeres mentés.");
                 return $this->goHome();
             }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing BugReport model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->is_admin) {
            return $this->goHome();
        }
        $model = $this->findModel($id);
        if ($model != null && Yii::$app->user->getIdentity()->id != $model->user_id) {
            throw new NotFoundHttpException();
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing BugReport model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->is_admin) {
            return $this->goHome();
        }
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the BugReport model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return BugReport the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BugReport::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
