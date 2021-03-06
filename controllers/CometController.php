<?php

namespace app\controllers;

use app\components\Flash;
use app\models\CommentSearch;
use app\models\forms\CometForm;
use app\models\forms\CommentForm;
use app\models\observations\Comet;
use Yii;
use app\models\Observe;
use app\models\ObserveSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * LandscapeController implements the CRUD actions for Observe model.
 */
class CometController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create', 'update', 'delete'],
                'rules' => [
                    [
                        'actions' => ['create', 'update'],
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
        $searchModel->type = Observe::TYPE_COMET;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Displays a single Observe model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $commentForm = new CommentForm();
        $commentForm->observation_id = $id;
        $commentData = (new CommentSearch(["observation_id" => $id]))->search();

        return $this->render('//observe/view', [
            'model' => $this->findModel($id),
            'commentForm' => $commentForm,
            'commentData' => $commentData,
        ]);
    }

    /**
     * Creates a new Observe model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CometForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->register()) {
                Flash::addSuccess("Sikeres művelet.");
                return $this->redirect(['view', 'id' => $model->id]);
            }

            Flash::addDanger("Belső hiba történt.");
        }

        return $this->render('//observe/create', [
            'model' => $model,
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
        $observe = Observe::find()->ofId($id)->ofUser(Yii::$app->user->getId())->one();

        if (empty($observe)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($observe->load(Yii::$app->request->post())) {
            if ($observe->image == null) {
                $cometForm = new CometForm();
                $cometForm->image = UploadedFile::getInstance($observe, 'image');

                $cometForm->uploadImage($observe->id);
            }
            if ($observe->save()) {
                return $this->redirect(['view', 'id' => $observe->id]);
            }
        }

        return $this->render('//observe/update', [
            'model' => $observe,
        ]);
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
        if (($model = Comet::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
