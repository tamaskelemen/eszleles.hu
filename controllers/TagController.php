<?php

namespace app\controllers;

use app\components\Flash;
use app\models\forms\MoonForm;
use app\models\ImageTag;
use app\models\observations\Moon;
use Yii;
use app\models\Observe;
use app\models\ObserveSearch;
use app\models\forms\DeepSkyForm;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * ObserveController implements the CRUD actions for Observe model.
 */
class TagController extends Controller
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
     * Lists all Observe models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ObserveSearch();
        $searchModel->type = Observe::TYPE_MOON;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionList()
    {
        $id = Yii::$app->request->post()['image_id'];
        $tags = ImageTag::find()->where(['image_id' => $id])->all();

        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $tags;
    }

    public function actionSearch()
    {
        $param = Yii::$app->request->post()['search'];
        $list = ImageTag::find()->where(['like', 'name', $param])->all();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return $list;
    }

    /**
     * Creates a new ImageTag model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->request->isPost) {
            return;
        }
        $model = new ImageTag();
        $data = Yii::$app->request->post();

        if ($model->load($data, "") && $model->save()) {
            return true;
        }

        return false;
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

        if (empty($observe)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        if ($observe->load(Yii::$app->request->post())) {

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
    public function actionDelete()
    {
        $this->findModel(Yii::$app->request->post()['id'])->delete();

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return true;
    }

    /**
     * Finds the Observe model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ImageTag the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ImageTag::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
