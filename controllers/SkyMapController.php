<?php

namespace app\controllers;

use app\components\SkyMap;
use app\models\ObservationSkyObject;
use app\models\Observe;
use app\models\SkyObject;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * The public map of the observed objects.
 */
class SkyMapController extends Controller
{
    const OBSERVATION_LIMIT = 12;

    /**
     * @return string
     */
    public function actionIndex()
    {
        $data = SkyMap::getData();

        return $this->render('index', [
            'stats' => $data['stats'],
            'gaps' => $data['gaps'],
            'missing' => $data['missing'],
            'unmapped' => $data['unmapped'],
        ]);
    }

    /**
     * Objects of the map.
     * @return \yii\web\Response
     */
    public function actionData()
    {
        return $this->asJson(SkyMap::getData()['objects']);
    }

    /**
     * Latest observations of an object.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionObject(int $id)
    {
        if (SkyObject::findOne($id) === null) {
            throw new NotFoundHttpException("A keresett oldal nem található");
        }

        $observationIds = ObservationSkyObject::find()
            ->select('observation_id')
            ->ofStatus(ObservationSkyObject::VISIBLE_STATUSES)
            ->andWhere(['sky_object_id' => $id]);

        $observations = Observe::find()
            ->where(['id' => $observationIds])
            ->with('thumbnail', 'observer')
            ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
            ->limit(self::OBSERVATION_LIMIT)
            ->all();

        return $this->asJson(array_map(function (Observe $observe) {
            return [
                'title' => $observe->object_name,
                'observer' => $observe->observer ? $observe->observer->name : '',
                'date' => substr((string)$observe->date, 0, 10),
                'url' => $observe->getViewUrl(),
                'image' => Url::to($observe->getThumbnailPath()),
            ];
        }, $observations));
    }
}
