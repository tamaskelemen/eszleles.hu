<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\User;
use Yii;
use yii\base\Model;

class PasswordChangeForm extends Model

{
    public $password;
    public $password_confirm;

    /**
     * @return array[]
     */
    public function rules()
    {
        return [
            [['password', 'password_confirm'], 'required', 'message' => 'A mezőt kötelező kitölteni.'],
            [['password', 'password_confirm'], 'string', 'min' => 6],

        ];
    }

    /**
     * @return string[]
     */
    public function attributeLabels()
    {
        return [
            'password' => 'Jelszó',
            'password_confirm' => 'Jelszó megerősítése',
        ];
    }

    /**
     * @return bool
     */
    public function change()
    {
        if (!$this->validate()) {
            return false;
        }

        if ($this->password != $this->password_confirm) {
            Flash::addWarning("A két jelszó nem egyezik meg.");
            return false;
        }

        $user = User::find()->where(['id'=> Yii::$app->user->getIdentity()->id])->one();
        $user->setPassword($this->password);

        if (!$user->save()) {
            Flash::addWarning("Belső hiba történt.");
            return false;
        }

        return true;

    }
}