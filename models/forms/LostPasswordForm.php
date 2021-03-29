<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\User;
use yii\base\Model;
use yii\db\Exception;

class LostPasswordForm extends Model
{
    public $email;

    public function rules()
    {
        return [
            ['email', 'email'],
            ['email', 'required', 'message' => 'A mezőt kötelező kitölteni'],
        ];
    }

    public function generateToken()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = User::findByEmail($this->email);

        if ($user == null) {
            Flash::addWarning("Nem található felhasználó a megadott email címmel");
            return false;
        }
        $user->generatePasswordResetToken();

        try {
            if (!$user->save()) {
                throw new \Exception("Belső hiba történt");
            }
        } catch (\Exception $e) {
            Flash::addWarning($e->getMessage());
            return false;
        }

        return true;
    }
}