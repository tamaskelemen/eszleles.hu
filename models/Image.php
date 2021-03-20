<?php

namespace app\models;

use app\models\Observe;
use Yii;

/**
 * This is the model class for table "images".
 *
 * @property int $id
 * @property string $path
 * @property int $observe_id
 * @property string $created_at
 */
class Image extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['path', 'observe_id'], 'required'],
            [['observe_id'], 'default', 'value' => null],
            [['observe_id'], 'integer'],
            [['created_at'], 'safe'],
            [['path'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'path' => 'Path',
            'observe_id' => 'Észlelés',
            'created_at' => 'Feltöltve',
        ];
    }

    public function getObserve()
    {
        return $this->hasOne(Observe::class, ['id' => 'observe_id']);
    }

    /**
     * {@inheritdoc}
     * @return ImagesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ImagesQuery(get_called_class());
    }
}
