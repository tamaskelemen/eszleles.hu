<?php
namespace app\models\forms;

use app\components\SocialLogin;
use app\models\User;
use Yii;
use yii\base\Model;
use yii\db\Expression;

/**
 * Finishes the registration with an external account.
 */
class SocialSignupForm extends Model
{
    public $name;
    public $email;
    public $terms;

    /**
     * @var array see SocialLogin::getProfile()
     */
    private $profile;

    /**
     * @param array $profile
     * @param array $config
     */
    public function __construct(array $profile, $config = [])
    {
        $this->profile = $profile;
        $this->name = $profile['name'];
        $this->email = $profile['email'];

        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Név',
            'email' => 'Email cím',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        // the one given by the provider can not be changed
        $email = $this->isEmailEditable() ? 'email' : '!email';

        return [
            [$email, 'trim'],
            [$email, 'required', 'message' => 'A mezőt kötelező kitölteni.'],
            [$email, 'email'],
            [$email, 'string', 'max' => 255],
            [$email, 'validateEmail'],

            ['name', 'trim'],
            ['name', 'required', 'message' => 'A mezőt kötelező kitölteni.'],
            ['name', 'string', 'max' => 255],

            ['terms', 'required', 'message' => 'A mezőt kötelező kitölteni.', 'requiredValue' => 1],
        ];
    }

    /**
     * @param string $attribute
     */
    public function validateEmail($attribute)
    {
        if (!$this->hasErrors() && User::isEmailTaken($this->email)) {
            $this->addError($attribute, 'Az email cím már használatban van.');
        }
    }

    /**
     * @return bool the provider does not always give an email address
     */
    public function isEmailEditable()
    {
        return $this->profile['email'] === null;
    }

    /**
     * Signs user up, without a password.
     *
     * @return User|null the saved model or null if the form is not valid
     * @throws \Exception
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        return Yii::$app->db->transaction(function () {
            $user = new User();
            $user->email = $this->email;
            $user->status = User::STATUS_NOT_MEMBER;
            $user->name = $this->name;
            $user->terms = new Expression("NOW()");
            $user->generateAuthKey();

            if (!$user->save()) {
                throw new \Exception("A felhasználót nem sikerült menteni.");
            }

            SocialLogin::connect($user, $this->profile);

            return $user;
        });
    }
}
