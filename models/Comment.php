<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comments".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $observation_id
 * @property int|null $parent_id the first level comment this one replies to, null if this is a first level one
 * @property string|null $created_at
 * @property string|null $comment
 *
 * @property Observe $observation
 * @property Comment|null $parent
 * @property Comment[] $replies
 * @property User $user
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'observation_id', 'parent_id'], 'default', 'value' => null],
            [['user_id', 'observation_id', 'parent_id'], 'integer'],
            [['comment'], 'string'],
            [['observation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Observe::class, 'targetAttribute' => ['observation_id' => 'id']],
            // replies can not be replied, only first level comments of the same observation
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => self::class, 'targetAttribute' => ['parent_id' => 'id', 'observation_id' => 'observation_id'], 'filter' => ['parent_id' => null]],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'observation_id' => 'Observation ID',
            'parent_id' => 'Parent ID',
            'created_at' => 'Created At',
            'comment' => 'Comment',
        ];
    }

    /**
     * Gets query for [[Observation]].
     *
     * @return \yii\db\ActiveQuery|ObservesQuery
     */
    public function getObservation()
    {
        return $this->hasOne(Observe::class, ['id' => 'observation_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Parent]].
     *
     * @return \yii\db\ActiveQuery|CommentsQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * Gets query for [[Replies]], oldest first.
     *
     * @return \yii\db\ActiveQuery|CommentsQuery
     */
    public function getReplies()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id'])->orderBy(['created_at' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * Everyone who should be notified about this comment, except the commenter.
     *
     * @return array notification types (`Notification::TYPE_*`) indexed by user id
     */
    public function getNotificationTypes()
    {
        $types = [];

        if ($this->observation->observer_id !== null) {
            $types[$this->observation->observer_id] = Notification::TYPE_COMMENT;
        }

        if ($this->parent !== null) {
            foreach ($this->parent->replies as $reply) {
                if ($reply->user_id !== null) {
                    $types[$reply->user_id] = Notification::TYPE_THREAD_REPLY;
                }
            }

            // the first level commenter may have replied too, but it is still their comment
            if ($this->parent->user_id !== null) {
                $types[$this->parent->user_id] = Notification::TYPE_REPLY;
            }
        }

        unset($types[$this->user_id]);

        return $types;
    }

    /**
     * {@inheritdoc}
     * @return CommentsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentsQuery(get_called_class());
    }
}
