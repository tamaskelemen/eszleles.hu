<?php

namespace app\controllers;

use app\components\Email;
use app\models\forms\SignupForm;
use app\models\Observe;
use Yii;
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
                'only' => ['logout'],
                'rules' => [
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
        $latestObs = Observe::find()->orderBy(['uploaded_at' => SORT_DESC])->limit(4)->all();

        return $this->render('index', [
            'latestObs' => $latestObs,
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
        }

        return $this->render('signup', [
            'signupForm' => $signupForm
        ]);
    }

    public function actionEmail()
    {
        Email::send("asd", "Új jelszó beállítása", "password-reset");

    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
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
