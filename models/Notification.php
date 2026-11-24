<?php

namespace app\models;

use yii\db\Exception;
use yii\db\Expression;

/**
 * This is the model class for table "notifications".
 *
 * Tells a user about a comment.
 *
 * @property int $id
 * @property int $user_id the user to be notified
 * @property int $comment_id
 * @property string $type why the user is notified
 * @property string $created_at
 * @property string|null $seen_at null if the notification is unread
 *
 * @property Comment $comment
 * @property User $user
 */
class Notification extends \yii\db\ActiveRecord
{
    const TYPE_COMMENT = 'comment';

    const PAGE_SIZE = 15;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'comment_id', 'type'], 'required'],
            [['user_id', 'comment_id'], 'integer'],
            [['type'], 'in', 'range' => array_keys(self::getAllTypes())],
            [['created_at', 'seen_at'], 'safe'],
            [['user_id', 'comment_id'], 'unique', 'targetAttribute' => ['user_id', 'comment_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Értesített felhasználó',
            'comment_id' => 'Hozzászólás',
            'type' => 'Típus',
            'created_at' => 'Létrehozva',
            'seen_at' => 'Olvasva',
        ];
    }

    /**
     * @return array texts that follow the name of the commenter and precede the name of the observation
     */
    public static function getAllTypes()
    {
        return [
            self::TYPE_COMMENT => 'hozzászólt az észlelésedhez:',
        ];
    }

    /**
     * Notifies everyone who should know about a new comment.
     *
     * @param Comment $comment
     * @throws Exception
     */
    public static function createForComment(Comment $comment)
    {
        foreach ($comment->getNotificationTypes() as $userId => $type) {
            $notification = new self();
            $notification->user_id = $userId;
            $notification->comment_id = $comment->id;
            $notification->type = $type;

            if (!$notification->save()) {
                throw new Exception("Az értesítés mentése nem sikerült.");
            }
        }
    }

    /**
     * @param int $userId
     * @return int
     */
    public static function countUnseen($userId)
    {
        return (int)self::find()->ofUser($userId)->unseen()->count();
    }

    /**
     * Marks the notifications of a user as seen.
     *
     * @param int|null $userId null for guests
     * @param int|null $observationId if set, only notifications about the comments of this observation are marked
     */
    public static function markSeen($userId, $observationId = null)
    {
        if ($userId === null) {
            return;
        }

        $condition = ['and', ['user_id' => $userId, 'seen_at' => null]];

        if ($observationId !== null) {
            $condition[] = ['comment_id' => Comment::find()->select('id')->where(['observation_id' => $observationId])];
        }

        self::updateAll(['seen_at' => new Expression('NOW()')], $condition);
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return self::getAllTypes()[$this->type];
    }

    /**
     * @return \yii\db\ActiveQuery|CommentsQuery
     */
    public function getComment()
    {
        return $this->hasOne(Comment::class, ['id' => 'comment_id']);
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
     * @return NotificationsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NotificationsQuery(get_called_class());
    }
}
