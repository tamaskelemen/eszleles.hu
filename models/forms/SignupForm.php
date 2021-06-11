<?php
namespace app\models\forms;

use yii\base\Model;
use app\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $name;
    public $email;
    public $password;
    public $password_confirm;
    public $terms;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Név',
            'email' => 'Email cím',
            'password' => 'Jelszó',
            'password_confirm' => 'Jelszó megerősítése',
            'terms' => 'A regisztrációval elismerem, hogy elolvastam és megértettem a <a href="/eula.pdf">VCSE Adatkezelési Tájékoztatóját</a>. ',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            ['email', 'trim'],
            ['email', 'required', 'message' => 'A mezőt kötelező kitölteni.'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'Az email cím már használatban van.'],

            ['password', 'required', 'message' => 'A mezőt kötelező kitölteni.'],
            ['password', 'string', 'min' => 6],

            ['password_confirm', 'required', 'message' => 'A mezőt kötelező kitölteni.'],
            ['password_confirm', 'compare', 'compareAttribute' => 'password'],

            ['name', 'required', 'message' => 'A mezőt kötelező kitölteni.'],
            ['name', 'string', 'max' => 255],

            ['terms', 'required', 'message' => 'A mezőt kötelező kitölteni.', 'requiredValue' => 1],
            ['terms', 'safe'],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->email = $this->email;
        $user->status = User::STATUS_NOT_MEMBER;
        $user->name = $this->name;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}
