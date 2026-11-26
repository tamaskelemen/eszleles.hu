<?php

namespace app\controllers;

use app\components\SkyMap;
use app\components\SkyPrograms;
use app\models\ObservationSkyObject;
use app\models\Observe;
use app\models\SkyObject;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * The public map of the observed objects.
 */
class SkyMapController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        // guests get back to the map after logging in to save an object
        if (Yii::$app->user->isGuest) {
            Yii::$app->user->setReturnUrl(Yii::$app->request->url);
        }

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
     * @param int|null $userId only the ones observed by this user
     * @return \yii\web\Response
     */
    public function actionData(?int $userId = null)
    {
        return $this->asJson($userId === null ? SkyMap::getData()['objects'] : SkyMap::findObjects($userId));
    }

    /**
     * Objects of an observing program, whether they are observed or not.
     * @param string $key
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionProgram(string $key)
    {
        if (!isset(SkyPrograms::getAll()[$key])) {
            throw new NotFoundHttpException("A keresett oldal nem található");
        }

        return $this->asJson(SkyMap::findProgramObjects($key));
    }

    /**
     * Observations of an object, the latest first.
     * @param int $id
     * @param int|null $userId only the observations of this user
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionObject(int $id, ?int $userId = null)
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
            ->andFilterWhere(['observer_id' => $userId])
            ->with('thumbnail', 'observer')
            ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
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
