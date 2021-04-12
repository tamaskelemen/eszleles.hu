<?php

namespace app\controllers;

use app\models\ImageTag;
use Yii;
use app\models\Observe;
use app\models\ObserveSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * TagController implements the CRUD actions for Image Tag model.
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

    public function actionList()
    {
        $id = Yii::$app->request->post()['image_id'];
        $tags = ImageTag::find()->where(['image_id' => $id])->all();

        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $tags;
    }

    public function actionSearch()
    {
        //TODO: this
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
        if (!Yii::$app->request->isPost || Yii::$app->user->isGuest) {
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
     * Deletes an existing Observe model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete()
    {
        $model = $this->findModel(Yii::$app->request->post()['id']);
        if ($model->image->observe->observer_id !== Yii::$app->user->identity->getId()) {
            return false;
        }

        $model->delete();

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
