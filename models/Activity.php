<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "activities".
 *
 * A single activity of a user, shown on the admin pages.
 *
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property int|null $observation_id the observation the activity is about, it may be deleted since
 * @property string|null $ip
 * @property string|null $user_agent
 * @property string $created_at
 *
 * @property Observe|null $observation
 * @property User $user
 */
class Activity extends \yii\db\ActiveRecord
{
    const TYPE_LOGIN = 'login';
    const TYPE_LOGIN_FAILED = 'login_failed';
    const TYPE_LOGIN_RESET_LINK = 'login_reset_link';
    // every provider of SocialLogin has its own types, they end with the id of the provider
    const TYPE_LOGIN_GOOGLE = 'login_google';
    const TYPE_CONNECT_GOOGLE = 'connect_google';
    const TYPE_LOGOUT = 'logout';
    const TYPE_OBSERVATION_CREATE = 'observation_create';
    const TYPE_OBSERVATION_UPDATE = 'observation_update';
    const TYPE_COMMENT = 'comment';
    const TYPE_IDENTIFICATION = 'identification';
    const TYPE_AVATAR_UPLOAD = 'avatar_upload';
    const TYPE_AVATAR_CHANGE = 'avatar_change';
    const TYPE_AVATAR_DELETE = 'avatar_delete';

    const USER_AGENT_LENGTH = 255;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activities';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'type'], 'required'],
            [['user_id', 'observation_id'], 'integer'],
            [['type'], 'in', 'range' => array_keys(self::getAllTypes())],
            [['ip'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => self::USER_AGENT_LENGTH],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Felhasználó',
            'type' => 'Tevékenység',
            'observation_id' => 'Észlelés',
            'ip' => 'IP cím',
            'user_agent' => 'Böngésző',
            'created_at' => 'Időpont (UTC)',
        ];
    }

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        return [
            self::TYPE_LOGIN => 'Sikeres belépés',
            self::TYPE_LOGIN_FAILED => 'Sikertelen belépés',
            self::TYPE_LOGIN_RESET_LINK => 'Belépés jelszó-visszaállító linkkel',
            self::TYPE_LOGIN_GOOGLE => 'Belépés Google-fiókkal',
            self::TYPE_CONNECT_GOOGLE => 'Google-fiók összekapcsolása',
            self::TYPE_LOGOUT => 'Kilépés',
            self::TYPE_OBSERVATION_CREATE => 'Észlelés feltöltése',
            self::TYPE_OBSERVATION_UPDATE => 'Észlelés módosítása',
            self::TYPE_COMMENT => 'Hozzászólás',
            self::TYPE_IDENTIFICATION => 'Azonosítás szerkesztése',
            self::TYPE_AVATAR_UPLOAD => 'Profilkép feltöltése',
            self::TYPE_AVATAR_CHANGE => 'Profilkép cseréje',
            self::TYPE_AVATAR_DELETE => 'Profilkép törlése',
        ];
    }

    /**
     * Logs an activity of a user. A failure is only written to the application log,
     * it must not break what the user is doing. For the same reason call it after the transaction is committed:
     * a failed insert would make the database reject the rest of the transaction.
     *
     * @param int $userId
     * @param string $type
     * @param int|null $observationId the observation the activity is about, if there is any
     */
    public static function log($userId, $type, $observationId = null)
    {
        try {
            $request = Yii::$app->request;

            $activity = new self();
            $activity->user_id = $userId;
            $activity->type = $type;
            $activity->observation_id = $observationId;
            $activity->ip = $request->userIP;
            // it can be anything, the database would reject invalid UTF-8
            $activity->user_agent = mb_substr(mb_scrub((string)$request->userAgent, 'UTF-8'), 0, self::USER_AGENT_LENGTH, 'UTF-8');

            if (!$activity->save()) {
                Yii::error(['Az aktivitás mentése nem sikerült.', $activity->errors], __METHOD__);
            }
        } catch (\Exception $e) {
            Yii::error($e, __METHOD__);
        }
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return self::getAllTypes()[$this->type] ?? $this->type;
    }

    /**
     * @return \yii\db\ActiveQuery|ObservesQuery
     */
    public function getObservation()
    {
        return $this->hasOne(Observe::class, ['id' => 'observation_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return ActivitiesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ActivitiesQuery(get_called_class());
    }
}
