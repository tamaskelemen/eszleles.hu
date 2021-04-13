<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "image_tags".
 *
 * @property int $id
 * @property int|null $image_id
 * @property int|null $name
 * @property string|null $coord_x
 * @property string|null $coord_y
 * @property string|null $created_at
 * @property string|null $annotation
 * @property string|null $annotation_id
 *
 * @property Image $image
 */
class ImageTag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image_tags';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'string'],
            [['image_id'], 'safe'],
            [['created_at'], 'safe'],
            [['annotation'], 'safe'],
            [['annotation_id'], 'safe'],
            [['coord_x', 'coord_y'], 'string', 'max' => 255],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'image_id' => 'Kép ID',
            'name' => 'Név',
            'coord_x' => 'Coord X',
            'coord_y' => 'Coord Y',
            'created_at' => 'Created At',
            'annotation' => 'Komment',
        ];
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery|ImagesQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

    /**
     * {@inheritdoc}
     * @return ImageTagsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ImageTagsQuery(get_called_class());
    }
}
