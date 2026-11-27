<?php

namespace app\controllers;

use app\components\Features;
use app\components\feed\UserObservationsSource;
use app\components\ObservingPlans;
use app\components\Badges;
use app\components\BadgeSync;
use app\components\Flash;
use app\components\SkyMap;
use app\models\ObservingPlan;
use app\models\forms\AvatarForm;
use app\models\forms\LocationForm;
use app\models\forms\PasswordChangeForm;
use app\models\Notification;
use app\models\Image;
use app\models\Observe;
use Yii;
use app\models\User;
use app\models\UserSearch;
use app\widgets\InfiniteList;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for Users model.
 */
class UserController extends Controller
{
    /** The observations on the profile: this many at first, then batches of OBSERVATIONS_BATCH */
    const OBSERVATIONS_FIRST = 20;
    const OBSERVATIONS_BATCH = 10;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'update', 'delete', 'change-password', 'avatar', 'delete-avatar', 'location', 'delete-location'],
                'rules' => [
                    [
                        'actions' => ['index', 'update', 'change-password', 'avatar', 'delete-avatar', 'location', 'delete-location'],
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
                    'delete-avatar' => ['POST'],
                    'delete-location' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionProfile($id)
    {
        $user = $this->findModel($id);
        $observationCount = (int)Observe::find()->ofUser($user->id)->count();
        // the latest one with an image gives the preview
        $previewObservation = Observe::find()
            ->ofUser($user->id)
            ->andWhere(['exists', Image::find()
                ->where(new Expression('observe_id = observations.id'))
                ->andWhere(['size' => Image::SIZE_THUMBNAIL])])
            ->orderBy(new Expression(UserObservationsSource::UPLOADED . ' DESC, id DESC'))
            ->one();
        $isOwn = (int)Yii::$app->user->id === $user->id;

        if ($isOwn) {
            $badges = Badges::findAll($user->id);
            // if something was missed, this repairs it
            BadgeSync::trySync($user->id, $badges);
            Notification::markBadgesSeen($user->id);
        } else {
            $badges = Badges::groupStored($user->badges);
        }

        // the others see the public ones only
        $plans = ObservingPlan::find()->ofUser($user->id)->orderBy(['updated_at' => SORT_DESC]);

        if (!$isOwn) {
            $plans->isPublic();
        }

        $showsPlans = Features::isEnabled(Features::OBSERVING_PLANS);
        $plans = $showsPlans ? $plans->all() : [];
        $planIds = array_column($plans, 'id');

        return $this->render('view', [
            'user' => $user,
            'isOwn' => $isOwn,
            'badges' => $badges,
            'showsPlans' => $showsPlans,
            'plans' => $plans,
            'planTotals' => ObservingPlans::countItems($planIds),
            // the progress of the owner of the profile, as the badges
            'planDone' => ObservingPlans::countDone($planIds, $user->id),
            'observationCount' => $observationCount,
            'previewObservation' => $previewObservation,
            'observationList' => [
                'source' => new UserObservationsSource($user->id),
                'url' => ['/user/observations', 'id' => $user->id],
                'first' => self::OBSERVATIONS_FIRST,
                'loadAll' => true,
            ],
            'skyMapStats' => SkyMap::getUserStats($user->id),
        ]);
    }

    /**
     * The next batch of the observations on the profile, see InfiniteList.
     * @param int $id
     * @param string|null $utana cursor of the batch
     * @param bool $osszes whether all the rest is asked for
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionObservations($id, $utana = null, $osszes = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = $this->findModel($id);

        return InfiniteList::respond(
            new UserObservationsSource($user->id),
            $utana,
            $osszes ? null : self::OBSERVATIONS_BATCH
        );
    }

//    /**
//     * Creates a new Users model.
//     * If creation is successful, the browser will be redirected to the 'view' page.
//     * @return mixed
//     */
//    public function actionCreate()
//    {
//        $model = new User();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        }
//
//        return $this->render('create', [
//            'model' => $model,
//        ]);
//    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if ($id != Yii::$app->user->getId()) {
            return $this->goHome();
        }
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['profile', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionChangePassword()
    {
        $user = Yii::$app->user->getIdentity();
        $pwChangeForm = new PasswordChangeForm();
        if ($pwChangeForm->load(Yii::$app->request->post()) && $pwChangeForm->change()) {
            Flash::addSuccess("A jelszavad sikeresen megváltozott.");
            return $this->redirect(['profile', 'id' => $user->id]);
        }

        return $this->render('password-change', [
            'model' => $pwChangeForm
        ]);
    }

    /**
     * Uploads or replaces the profile picture of the logged in user.
     * @return string|\yii\web\Response
     */
    public function actionAvatar()
    {
        $user = Yii::$app->user->getIdentity();
        $model = new AvatarForm();

        if (Yii::$app->request->isPost && $model->upload($user)) {
            Flash::addSuccess("A profilképedet elmentettük.");
            return $this->redirect(['profile', 'id' => $user->id]);
        }

        return $this->render('avatar', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionDeleteAvatar()
    {
        $user = Yii::$app->user->getIdentity();
        AvatarForm::delete($user);
        Flash::addSuccess("A profilképedet töröltük.");

        return $this->redirect(['avatar']);
    }

    /**
     * Sets the place the recommender of the home page calculates with.
     * @return string|\yii\web\Response
     */
    public function actionLocation()
    {
        $user = Yii::$app->user->getIdentity();
        $model = LocationForm::fromUser($user);

        if ($model->load(Yii::$app->request->post()) && $model->save($user)) {
            Flash::addSuccess("A helyszínt elmentettük, az ajánló ezentúl ezzel számol.");
            return $this->redirect(['location']);
        }

        return $this->render('location', [
            'model' => $model,
            'user' => $user,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionDeleteLocation()
    {
        LocationForm::delete(Yii::$app->user->getIdentity());
        Flash::addSuccess("A helyszínt töröltük, az ajánló újra Budapesttel számol.");

        return $this->redirect(['location']);
    }

    /**
     * Deletes an existing Users model.
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
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
