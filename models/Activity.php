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
 * @property string|null $ip
 * @property string|null $user_agent
 * @property string $created_at
 *
 * @property User $user
 */
class Activity extends \yii\db\ActiveRecord
{
    const TYPE_LOGIN = 'login';
    const TYPE_LOGIN_FAILED = 'login_failed';
    const TYPE_LOGIN_RESET_LINK = 'login_reset_link';

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
            [['user_id'], 'integer'],
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
        ];
    }

    /**
     * Logs an activity of a user. A failure is only written to the application log,
     * it must not break what the user is doing.
     *
     * @param int $userId
     * @param string $type
     */
    public static function log($userId, $type)
    {
        try {
            $request = Yii::$app->request;

            $activity = new self();
            $activity->user_id = $userId;
            $activity->type = $type;
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
