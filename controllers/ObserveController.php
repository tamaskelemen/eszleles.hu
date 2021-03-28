<?php

namespace app\controllers;

use app\components\Flash;
use app\models\forms\CommentForm;
use Yii;
use app\models\Observe;
use app\models\ObserveSearch;
use app\models\forms\DeepSkyForm;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ObserveController implements the CRUD actions for Observe model.
 */
class ObserveController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['create', 'update', 'delete', 'comment'],
                'rules' => [
                    [
                        'actions' => ['create', 'update', 'comment'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['delete'],
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
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Observe models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ObserveSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing Observe model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $observe = Observe::find()->ofId($id)->ofUser(Yii::$app->user->id)->one();

        $deepSkyForm = new DeepSkyForm();

        $deepSkyForm->setAttributes($observe->attributes);

        if (empty($deepSkyForm)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($deepSkyForm->load(Yii::$app->request->post())) {

            if ($deepSkyForm->register()) {
                return $this->redirect(['view', 'id' => $deepSkyForm->id]);
            }
        }

        return $this->render('update', [
            'model' => $deepSkyForm,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionComment()
    {
        $commentForm = new CommentForm();

        if ($commentForm->load(Yii::$app->request->post()) && $commentForm->add()) {

            Flash::addSuccess("A hozzászólás sikeresen mentve.");
            return $this->redirect(['/' . $commentForm->observation_id]);
        }

        return $this->goBack();
    }

    /**
     * Deletes an existing Observe model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Observe model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Observe the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Observe::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
