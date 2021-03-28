<?php

namespace app\models;

use Yii;
use yii\base\NotSupportedException;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string|null $email
 * @property string|null $name
 * @property string|null $last_login
 * @property string|null $password_hash
 * @property string|null $password_reset_token
 * @property string|null $auth_key
 * @property int|null $status
 * @property string|null $terms
 * @property bool|null $newsletter
 * @property string|null $created_at
 * @property bool $is_admin
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    const STATUS_NOT_MEMBER = 0;
    const STATUS_MEMBER = 1;
    const STATUS_DELETED = -1;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'last_login', 'terms', 'name'], 'safe'],
            ['status', 'in', 'range' => [self::STATUS_MEMBER, self::STATUS_NOT_MEMBER]],
            [['status'], 'integer'],
            [['email', 'password_hash', 'password_reset_token', 'auth_key'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email cím',
            'created_at' => 'Regisztráció ideje',
            'last_login' => 'Utolsó belépés',
            'name' => 'Észlelő',
            'status' => 'Státusz',
            'terms' => 'EULA',
            'newsletter' => 'Hírlevelek',
            'password' => 'Jelszó',
            'is_admin' => 'Adminisztrátor',
        ];
    }

    public static function tableName()
    {
        return 'users';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email]);
    }


    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }


    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        if ($this->is_admin) {
            return true;
        }

        return false;
    }

    public function getId()
    {
        return $this->id;
    }
}
