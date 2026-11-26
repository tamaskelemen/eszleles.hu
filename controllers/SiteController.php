<?php

namespace app\controllers;

use app\components\Email;
use app\components\Flash;
use app\components\recommender\Recommender;
use app\components\SocialAuthAction;
use app\components\SocialLogin;
use app\models\Activity;
use app\models\CommentSearch;
use app\models\forms\LostPasswordForm;
use app\models\forms\SignupForm;
use app\models\forms\SocialSignupForm;
use app\models\Observe;
use app\models\User;
use Yii;
use yii\authclient\ClientInterface;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'auth', 'social-signup', 'social-connect'],
                'rules' => [
                    [
                        'actions' => ['auth', 'social-signup', 'social-connect'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['auth', 'social-signup', 'social-connect'],
                        'allow' => false,
                        'denyCallback' => function ($rule, $action) {
                            return $this->goHome();
                        }
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'social-connect' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'auth' => [
                'class' => SocialAuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
                'cancelCallback' => [$this, 'onAuthCancel'],
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     */
    public function actionObservation($id)
    {
        $observation = Observe::find()->ofId($id)->one();
        if ($observation == null) {
            throw new NotFoundHttpException("The requested page does not exists");
        }

        $controller = $observation->type;
        return $this->redirect(["/{$controller}/view", "id" => $id]);
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $latestObs = Observe::find()->orderBy(['uploaded_at' => SORT_DESC])->limit(8)->with('observer', 'comments', 'thumbnail')->all();
        $commentData = (new CommentSearch())->setLimit(4)->search();
        $commentData->pagination = false;

        return $this->render('index', [
            'latestObs' => $latestObs,
            'commentData' => $commentData,
            'recommender' => new Recommender(RecommendController::createContext()),
            ]
        );
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            SocialLogin::connectPending($model->getUser());
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $signupForm = new SignupForm();

        if ($signupForm->load(Yii::$app->request->post())) {
            try {
                if (!$signupForm->signup()) {
                    throw new Exception("Failed to sign up user");
                }
                Yii::$app->session->set('success', 'Sikeres regisztráció!');
            } catch (\Exception $e) {
                Yii::$app->session->set('error', 'Belső hiba történt.');
            }

            return $this->goHome();
        }

        return $this->render('signup', [
            'signupForm' => $signupForm
        ]);
    }

    /**
     * Called after a successful login at the provider.
     *
     * @param ClientInterface $client
     * @return Response
     * @throws \Exception
     */
    public function onAuthSuccess(ClientInterface $client)
    {
        $profile = SocialLogin::getProfile($client);
        // nothing else is requested from the provider, so the access token should not stay in the session
        $client->setAccessToken(null);

        $account = SocialLogin::findAccount($profile);

        if ($account !== null) {
            $account->updateAttributes($profile);
            SocialLogin::clearPending();

            if (!SocialLogin::login($account->user, $account->provider)) {
                throw new Exception("Failed to log in user");
            }
            return $this->goBack('/');
        }

        SocialLogin::setPending($profile);

        if ($profile['email'] !== null && User::isEmailTaken($profile['email'])) {
            $name = $client->getTitle();
            Flash::addInfo("Ezzel az email címmel ({$profile['email']}) már van profil az oldalon. Lépj be a jelszavaddal, és összekapcsoljuk a {$name} fiókoddal. Ha elfelejtetted a jelszavad, kérj újat.");
            return $this->redirect(['login']);
        }

        return $this->redirect(['social-signup']);
    }

    /**
     * Called if the user cancels the login at the provider.
     *
     * @param ClientInterface $client
     * @return Response
     */
    public function onAuthCancel(ClientInterface $client)
    {
        Flash::addInfo("A belépést megszakítottad.");

        return $this->redirect(['login']);
    }

    /**
     * Finishes the registration with an external account.
     *
     * @return Response|string
     */
    public function actionSocialSignup()
    {
        $profile = SocialLogin::getPending();

        if ($profile === null) {
            return $this->redirect(['signup']);
        }

        $model = new SocialSignupForm($profile);

        if ($model->load(Yii::$app->request->post())) {
            try {
                $user = $model->signup();

                if ($user !== null) {
                    SocialLogin::clearPending();
                    SocialLogin::login($user, $profile['provider']);
                    Flash::addSuccess("Sikeres regisztráció!");
                    return $this->goBack('/');
                }
            } catch (\Exception $e) {
                Yii::error($e, __METHOD__);
                Flash::addDanger("Belső hiba történt.");
            }
        }

        return $this->render('social-signup', [
            'model' => $model,
            'providerName' => SocialLogin::getProviderName($profile['provider']),
        ]);
    }

    /**
     * The external account will be connected to the existing account the user logs in to.
     *
     * @return Response
     */
    public function actionSocialConnect()
    {
        SocialLogin::requestConnect();

        return $this->redirect(['login']);
    }

    public function actionLostPassword()
    {
        $lostPasswordForm = new LostPasswordForm();
        if (
            $lostPasswordForm->load(Yii::$app->request->post())
            && $lostPasswordForm->generateToken()
        ) {
            $user = User::findByEmail($lostPasswordForm->email);
            $result = Email::send(
                $user->email,
                "Elfelejtett jelszó",
                "password-reset",
                ['token' => $user->password_reset_token]
            );

            if ($result) {
                Flash::addSuccess("Emailben elküldtük a linket, ahol beállíthatja új jelszavát.");
                return $this->goHome();
            }
            Flash::addWarning("Az email elküldése közben hiba történt");
        }

        return $this->render('lost-password', ['model' => $lostPasswordForm]);
    }

    public function actionNewPassword($token)
    {
        $user = User::findByPasswordResetToken($token);

        if ($user == null) {
            throw new NotFoundHttpException("A keresett oldal nem található.");
        }
        if (Yii::$app->user->login($user)) {
            Activity::log($user->id, Activity::TYPE_LOGIN_RESET_LINK);
            SocialLogin::connectPending($user);
            $this->redirect("/user/change-password");

        }

    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        $userId = Yii::$app->user->id;

        if (Yii::$app->user->logout()) {
            Activity::log($userId, Activity::TYPE_LOGOUT);
        }

        return $this->goHome();
    }

    /**
     * @return string
     */
    public function actionFaq()
    {
        return $this->render("faq");
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
