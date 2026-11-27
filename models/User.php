<?php

namespace app\models;

use app\components\Helper;
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
 * @property string $facebook
 * @property string $instagram
 * @property string $website
 * @property string $github
 * @property string|null $twitter X (Twitter)
 * @property string|null $threads
 * @property string|null $astrobin
 * @property string $introduction
 * @property string|null $avatar_path the profile picture, see getAvatarUrl()
 * @property string|null $avatar_source see AVATAR_SOURCE_*, or the id of the auth client the picture is copied from
 * @property string|null $avatar_source_url the external picture the copy is made from
 * @property string|null $location_name the place of the recommender, see LocationForm
 * @property double|null $location_latitude
 * @property double|null $location_longitude positive to the east
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    const STATUS_NOT_MEMBER = 0;
    const STATUS_MEMBER = 1;
    const STATUS_DELETED = -1;

    const DEFAULT_AVATAR = '/pictures/avatar.svg';
    const AVATAR_SOURCE_UPLOAD = 'upload';
    const AVATAR_SOURCE_REMOVED = 'removed';

    /** profile links, in display order; each has an icon in web/pictures/icons/<attribute>.svg */
    const LINK_ATTRIBUTES = ['website', 'facebook', 'instagram', 'twitter', 'threads', 'astrobin', 'github'];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'newsletter'], 'safe'],
            // attributes marked with ! are set by the application only, they can not be loaded from a request
            ['!status', 'in', 'range' => [self::STATUS_MEMBER, self::STATUS_NOT_MEMBER]],
            [['!status'], 'integer'],
            [['email', '!password_hash', '!password_reset_token', '!auth_key'], 'string', 'max' => 255],
            ['introduction', 'string'],
            [self::LINK_ATTRIBUTES, 'trim'],
            [self::LINK_ATTRIBUTES, 'string', 'max' => 255],
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
            'website' => 'Weboldal',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'github' => 'GitHub',
            'twitter' => 'X (Twitter)',
            'threads' => 'Threads',
            'astrobin' => 'AstroBin',
            'introduction' => 'Bemutatkozás',
        ];
    }

    /**
     * @return array|null name, latitude and longitude of the place the recommender calculates with, null if not set
     */
    public function getLocation()
    {
        if ($this->location_latitude === null || $this->location_longitude === null) {
            return null;
        }

        return [
            'name' => $this->location_name,
            'latitude' => (float)$this->location_latitude,
            'longitude' => (float)$this->location_longitude,
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['user_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery|UserBadgesQuery
     */
    public function getBadges()
    {
        return $this->hasMany(UserBadge::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|SocialAccountsQuery
     */
    public function getSocialAccounts()
    {
        return $this->hasMany(SocialAccount::class, ['user_id' => 'id']);
    }

    public function getObservations()
    {
        return $this->hasMany(Observe::class, ['observer_id' => 'id']);
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
     * @param string $email
     * @return bool whether a user has the address, regardless of the case of the letters
     */
    public static function isEmailTaken($email)
    {
        return static::find()->where(['lower(email)' => mb_strtolower($email)])->exists();
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
        // the users registered with an external account have no password
        return !empty($this->password_hash)
            && Yii::$app->security->validatePassword($password, $this->password_hash);
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

    /**
     * @return string[] the filled profile links that are web URLs, keyed by attribute, see LINK_ATTRIBUTES
     */
    public function getSocialLinks()
    {
        $links = [];
        foreach (self::LINK_ATTRIBUTES as $attribute) {
            if (Helper::isWebUrl($this->$attribute)) {
                $links[$attribute] = $this->$attribute;
            }
        }
        return $links;
    }

    /**
     * @return string the uploaded profile picture, or the default one
     */
    public function getAvatarUrl()
    {
        return $this->avatar_path ?: self::DEFAULT_AVATAR;
    }

    /**
     * @return bool whether the picture of an external account may be copied as the profile picture
     */
    public function canCopyAvatar()
    {
        return !in_array($this->avatar_source, [self::AVATAR_SOURCE_UPLOAD, self::AVATAR_SOURCE_REMOVED], true);
    }

    /**
     * @return string|null how long the user has been a member, e.g. "2 éve és 3 hónapja tag", null if unknown
     */
    public function getMembershipDuration()
    {
        if (!$this->created_at) {
            return null;
        }

        $diff = (new \DateTime($this->created_at))->diff(new \DateTime());

        if ($diff->invert) {
            return 'Ma csatlakozott';
        }

        if ($diff->y > 0) {
            return $diff->m > 0 ? "{$diff->y} éve és {$diff->m} hónapja tag" : "{$diff->y} éve tag";
        }

        if ($diff->m > 0) {
            return "{$diff->m} hónapja tag";
        }

        if ($diff->d > 0) {
            return "{$diff->d} napja tag";
        }

        return 'Ma csatlakozott';
    }
}
