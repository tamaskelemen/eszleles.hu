<?php

namespace app\models;

use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * This is the model class for table "notifications".
 *
 * Tells a user about a comment or an earned badge.
 *
 * @property int $id
 * @property int $user_id the user to be notified
 * @property int|null $comment_id null if it is about a badge
 * @property int|null $badge_id null if it is about a comment
 * @property string $type why the user is notified
 * @property string $created_at
 * @property string|null $seen_at null if the notification is unread
 *
 * @property Comment|null $comment
 * @property UserBadge|null $badge
 * @property User $user
 */
class Notification extends \yii\db\ActiveRecord
{
    const TYPE_COMMENT = 'comment';
    const TYPE_REPLY = 'reply';
    const TYPE_THREAD_REPLY = 'thread_reply';
    const TYPE_BADGE = 'badge';

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
            [['user_id', 'type'], 'required'],
            [['comment_id'], 'required', 'when' => function (self $model) {
                return $model->type !== self::TYPE_BADGE;
            }],
            [['badge_id'], 'required', 'when' => function (self $model) {
                return $model->type === self::TYPE_BADGE;
            }],
            [['user_id', 'comment_id', 'badge_id'], 'integer'],
            [['type'], 'in', 'range' => array_keys(self::getAllTypes())],
            [['created_at', 'seen_at'], 'safe'],
            [['comment_id'], 'unique', 'targetAttribute' => ['user_id', 'comment_id']],
            [['badge_id'], 'unique'],
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
            'badge_id' => 'Jelvény',
            'type' => 'Típus',
            'created_at' => 'Létrehozva',
            'seen_at' => 'Olvasva',
        ];
    }

    /**
     * @return array texts that precede the name of the observation or the badge, the ones about a comment follow the name
     * of the commenter
     */
    public static function getAllTypes()
    {
        return [
            self::TYPE_COMMENT => 'hozzászólt az észlelésedhez:',
            self::TYPE_REPLY => 'válaszolt a hozzászólásodra:',
            self::TYPE_THREAD_REPLY => 'válaszolt egy beszélgetésben, amelyhez hozzászóltál:',
            self::TYPE_BADGE => 'Új jelvényt szereztél:',
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
     * Notifies a user about a new badge.
     *
     * @param UserBadge $badge
     * @throws Exception
     */
    public static function createForBadge(UserBadge $badge)
    {
        // another request may be notifying about the same one
        self::getDb()->createCommand()->upsert(
            self::tableName(),
            ['user_id' => $badge->user_id, 'badge_id' => $badge->id, 'type' => self::TYPE_BADGE],
            false
        )->execute();
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
     * Marks the notifications of a user about badges as seen.
     *
     * @param int $userId
     */
    public static function markBadgesSeen($userId)
    {
        self::updateAll(['seen_at' => new Expression('NOW()')], ['user_id' => $userId, 'seen_at' => null, 'type' => self::TYPE_BADGE]);
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return self::getAllTypes()[$this->type];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->type === self::TYPE_BADGE) {
            return Url::toRoute(['/user/profile', 'id' => $this->user_id, '#' => 'badges-' . $this->badge->getCategoryData()['group']]);
        }

        return $this->comment->observation->getViewUrl() . '#hozzaszolas-' . $this->comment_id;
    }

    /**
     * @return \yii\db\ActiveQuery|CommentsQuery
     */
    public function getComment()
    {
        return $this->hasOne(Comment::class, ['id' => 'comment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|UserBadgesQuery
     */
    public function getBadge()
    {
        return $this->hasOne(UserBadge::class, ['id' => 'badge_id']);
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
