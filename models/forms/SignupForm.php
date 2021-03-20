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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'Az email cím már használatban van.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['password_confirm', 'required'],
            ['password_confirm', 'compare', 'compareAttribute' => 'password'],

            ['name', 'required'],
            ['name', 'string', 'max' => 255],
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
