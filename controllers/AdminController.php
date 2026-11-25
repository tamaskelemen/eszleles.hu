<?php
namespace app\controllers;


use app\components\Flash;
use app\components\SkyLinker;
use app\components\SkyMap;
use app\models\Activity;
use app\models\ActivitySearch;
use app\models\IdentificationSearch;
use app\models\ObservationSkyObject;
use app\models\Observe;
use app\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Url;
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
                    'identification-bulk' => ['POST'],
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

    /**
     * Lists the observations with the sky objects they are about.
     * @return string
     */
    public function actionIdentification()
    {
        $searchModel = new IdentificationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Url::remember('', 'identification');

        return $this->render('identification', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'counts' => IdentificationSearch::getCounts(),
        ]);
    }

    /**
     * Edits the sky objects of an observation.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionIdentify(int $id)
    {
        $observe = Observe::findOne($id);

        if ($observe === null) {
            throw new NotFoundHttpException("A keresett oldal nem található");
        }

        $backUrl = Url::previous('identification') ?? Url::to(['/admin/identification']);
        $data = Yii::$app->request->post($observe->formName(), []);

        // the title is posted by the sky object input, without JavaScript an empty list would be saved
        if (isset($data['skyObjectTitle'])) {
            Yii::$app->db->transaction(function () use ($observe, $data) {
                $items = (array)($data['skyObjectItems'] ?? []);

                (new SkyLinker())->confirm($observe, $items);
                $observe->setHasNoSkyObject(empty($items));
            });

            Activity::log(Yii::$app->user->id, Activity::TYPE_IDENTIFICATION, $observe->id);
            Flash::addSuccess("Az azonosítást elmentettük: " . $observe->object_name);

            return $this->redirect($backUrl);
        }

        return $this->render('identify', [
            'model' => $observe,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Approves the objects of the selected observations, or marks that they have no object.
     * @return \yii\web\Response
     */
    public function actionIdentificationBulk()
    {
        $request = Yii::$app->request;
        $ids = array_filter((array)$request->post('ids', []), 'ctype_digit');
        $observations = Observe::find()->where(['id' => $ids])->all();
        $isDismiss = $request->post('action') === 'dismiss';

        foreach ($observations as $observe) {
            if ($isDismiss) {
                $observe->setHasNoSkyObject(true);
            } else {
                ObservationSkyObject::updateAll(
                    ['status' => ObservationSkyObject::STATUS_CONFIRMED, 'reason' => null],
                    ['observation_id' => $observe->id, 'status' => ObservationSkyObject::STATUS_REVIEW]
                );
            }

            Activity::log(Yii::$app->user->id, Activity::TYPE_IDENTIFICATION, $observe->id);
        }

        // updateAll() does not do it
        SkyMap::clearCache();

        Flash::addSuccess("Elmentett észlelések száma: " . count($observations));

        return $this->redirect(Url::previous('identification') ?? ['/admin/identification']);
    }

}