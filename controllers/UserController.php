<?php

namespace app\controllers;

use app\components\ObservingPlans;
use app\components\Badges;
use app\components\BadgeSync;
use app\components\Flash;
use app\components\SkyMap;
use app\models\ObservingPlan;
use app\models\forms\AvatarForm;
use app\models\forms\PasswordChangeForm;
use app\models\Notification;
use app\models\Observe;
use Yii;
use app\models\User;
use app\models\UserSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for Users model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'update', 'delete', 'change-password', 'avatar', 'delete-avatar'],
                'rules' => [
                    [
                        'actions' => ['index', 'update', 'change-password', 'avatar', 'delete-avatar'],
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
        $observations = Observe::find()->where(['observer_id' => $user->id])->orderBy(['uploaded_at' => SORT_DESC])->with('observer', 'comments', 'thumbnail')->all();
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

        $plans = $plans->all();
        $planIds = array_column($plans, 'id');

        return $this->render('view', [
            'user' => $user,
            'isOwn' => $isOwn,
            'badges' => $badges,
            'plans' => $plans,
            'planTotals' => ObservingPlans::countItems($planIds),
            // the progress of the owner of the profile, as the badges
            'planDone' => ObservingPlans::countDone($planIds, $user->id),
            'observations' => $observations,
            'skyMapStats' => SkyMap::getUserStats($user->id),
        ]);
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
