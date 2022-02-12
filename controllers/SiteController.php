<?php

namespace app\controllers;

use app\components\Email;
use app\components\Flash;
use app\models\CommentSearch;
use app\models\forms\LostPasswordForm;
use app\models\forms\SignupForm;
use app\models\Observe;
use app\models\User;
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
        $latestObs = Observe::find()->orderBy(['uploaded_at' => SORT_DESC])->limit(4)->with('observer', 'comments', 'thumbnail')->all();
        $commentData = (new CommentSearch())->setLimit(4)->search();
        $commentData->pagination = false;

        return $this->render('index', [
            'latestObs' => $latestObs,
            'commentData' => $commentData,
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

            return $this->goHome();
        }

        return $this->render('signup', [
            'signupForm' => $signupForm
        ]);
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
        Yii::$app->user->logout();

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
