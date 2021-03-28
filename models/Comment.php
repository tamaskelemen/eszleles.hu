<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comments".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $observation_id
 * @property string|null $created_at
 * @property string|null $comment
 *
 * @property Observe $observation
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
            [['user_id', 'observation_id'], 'default', 'value' => null],
            [['user_id', 'observation_id'], 'integer'],
            [['comment'], 'string'],
            [['observation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Observe::class, 'targetAttribute' => ['observation_id' => 'id']],
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
     * {@inheritdoc}
     * @return CommentsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentsQuery(get_called_class());
    }
}
