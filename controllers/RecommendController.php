<?php

namespace app\controllers;

use app\components\recommender\Context;
use app\components\recommender\Recommender;
use app\components\SkyPrograms;
use app\models\ObservationSkyObject;
use app\models\Observe;
use app\models\SkyObject;
use Yii;
use yii\db\Query;
use yii\web\Controller;

/**
 * The recommender of the home page: what to observe now.
 */
class RecommendController extends Controller
{
    /** Recently shown objects sent back by the page, they are not recommended again */
    const EXCLUDE_LIMIT = 20;

    /** Thumbnails on the card */
    const IMAGE_LIMIT = 4;

    /**
     * A recommendation for the filters of the request. The current object is kept if it still passes them.
     *
     * @return \yii\web\Response
     */
    public function actionNext()
    {
        $request = Yii::$app->request;
        $recommender = new Recommender(self::createContext());
        $input = $request->get('f');
        $values = $recommender->parseValues(is_array($input) ? $input : []);
        $currentId = self::parseId($request->get('current'));
        $excludeIds = array_slice(array_filter(array_map([self::class, 'parseId'], explode(',', (string)$request->get('exclude')))), 0, self::EXCLUDE_LIMIT);

        $object = $recommender->recommend($values, $currentId, $excludeIds);

        return $this->asJson([
            'id' => $object === null ? null : $object->id,
            'html' => $this->renderPartial('_card', [
                'object' => $object,
                'context' => $recommender->context,
                'count' => $recommender->count($values),
            ] + ($object === null ? [] : $this->findCardData($object))),
        ]);
    }

    /**
     * @return Context
     */
    public static function createContext()
    {
        $user = Yii::$app->user;

        if ($user->isGuest) {
            return new Context();
        }

        /** @var \app\models\User $identity */
        $identity = $user->getIdentity();

        return new Context((int)$user->id, $identity->getLocation());
    }

    /**
     * @param SkyObject $object
     * @return array designations, programs, observations (of the user), images (observations with an image),
     * observationCount
     */
    private function findCardData(SkyObject $object)
    {
        $observations = Observe::find()
            ->where(['id' => ObservationSkyObject::find()
                ->select('observation_id')
                ->ofStatus(ObservationSkyObject::VISIBLE_STATUSES)
                ->andWhere(['sky_object_id' => $object->id])])
            ->with('thumbnail', 'observer')
            ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
            ->all();
        $userId = Yii::$app->user->isGuest ? null : (int)Yii::$app->user->id;
        $programs = SkyPrograms::getAll();
        $lists = (new Query())->select('list')->distinct()->from('sky_list_items')->where(['sky_object_id' => $object->id])->column();

        return [
            'designations' => array_values(array_diff($object->getDesignations(), [$object->getDisplayName()])),
            'programs' => array_values(array_map(function ($list) use ($programs) {
                return $programs[$list]['name'];
            }, array_intersect(array_keys($programs), $lists))),
            'observations' => $userId === null ? [] : array_values(array_filter($observations, function (Observe $observe) use ($userId) {
                return (int)$observe->observer_id === $userId;
            })),
            'images' => array_slice(array_values(array_filter($observations, function (Observe $observe) {
                return $observe->thumbnail !== null;
            })), 0, self::IMAGE_LIMIT),
            'observationCount' => count($observations),
        ];
    }

    /**
     * @param mixed $value
     * @return int|null
     */
    private static function parseId($value)
    {
        // the column is a 32 bit integer
        return is_string($value) && ctype_digit($value) && strlen($value) < 10 ? (int)$value : null;
    }
}
