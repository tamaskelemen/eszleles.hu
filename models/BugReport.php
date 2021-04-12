<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "bug_reports".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $type
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class BugReport extends \yii\db\ActiveRecord
{
    const TYPE_FEATURE = 1;
    const TYPE_BUG = 2;

    const STATUS_CLOSED = 0;
    const STATUS_OPEN = 1;
    const STATUS_WIP = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bug_reports';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description', 'type'], 'required'],
            [['title', 'description', 'type'], 'string'],
            [['user_id', 'status'], 'integer'],
            [['user_id', 'status'], 'default', 'value' => null],
            [['created_at', 'updated_at'], 'safe'],
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
            'user_id' => 'Jelentő',
            'title' => 'Összefoglalás röviden',
            'description' => 'Leírás',
            'type' => 'Típus',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_CLOSED => "closed",
            self::STATUS_OPEN => "open",
            self::STATUS_WIP => "WIP",
        ];
    }

    public static function getTypes()
    {
        return [
            self::TYPE_FEATURE => "feature",
            self::TYPE_BUG => "bug",
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->created_at = new Expression("NOW()");
        }

        $this->updated_at = new Expression("NOW()");

        return parent::beforeSave($insert);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery|UsersQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return BugReportsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BugReportsQuery(get_called_class());
    }
}
