<?php

namespace app\models;

/**
 * This is the model class for table "image_versions".
 *
 * An observation can have multiple image versions, the latest one is the main image.
 *
 * @property int $id
 * @property int $observe_id
 * @property string $created_at
 *
 * @property Observe $observe
 * @property Image $image
 * @property Image $thumbnail
 */
class ImageVersion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image_versions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['observe_id'], 'required'],
            [['observe_id'], 'integer'],
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
            'observe_id' => 'Észlelés',
            'created_at' => 'Feltöltve',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObserve()
    {
        return $this->hasOne(Observe::class, ['id' => 'observe_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::class, ['image_version_id' => 'id'])->andWhere(['size' => Image::SIZE_ORIGINAL]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getThumbnail()
    {
        return $this->hasOne(Image::class, ['image_version_id' => 'id'])->andWhere(['size' => Image::SIZE_THUMBNAIL]);
    }
}
