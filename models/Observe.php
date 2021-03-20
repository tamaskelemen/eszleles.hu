<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "observes".
 *
 * @property int $id
 * @property string|null $object_name
 * @property string|null $catalog_number
 * @property string|null $constellation
 * @property string|null $object_type
 * @property string|null $telescope
 * @property string|null $camera
 * @property int|null $seeing
 * @property int|null $transparency
 * @property string|null $location
 * @property string|null $source
 * @property string|null $date
 * @property int|null $observer_id
 * @property string|null $description
 * @property string|null $uploaded_at
 * @property string|null $edited_at
 * @property string $type
 *
 * @property User $observer
 */
class Observe extends \yii\db\ActiveRecord
{
    const TYPE_DEEP_SKY = "deepsky";
    const TYPE_MOON = "moon";
    const TYPE_PLANET = "planet";
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'observes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['constellation', 'object_type', 'telescope', 'location', 'description'], 'required', 'message' => "A mezőt kötelező kitölteni!"],
            [['seeing', 'transparency', 'observer_id'], 'default', 'value' => null],
            [['seeing', 'transparency', 'observer_id'], 'integer'],
            [['seeing'], 'in', 'range' => ['min' => 1, 'max' => 10]],
            [['transparency'], 'in', 'range' => ['min' => 1, 'max' => 5]],
            [['date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['uploaded_at', 'edited_at'], 'date'],
            [['description', 'type'], 'string'],
            [['object_name', 'catalog_number', 'constellation', 'object_type', 'telescope', 'camera', 'location'], 'string', 'max' => 255],
            [['observer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['observer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_name' => 'Név',
            'catalog_number' => 'Katalógus szám',
            'constellation' => 'Csillagkép',
            'object_type' => 'Típus',
            'telescope' => 'Távcső',
            'camera' => 'Kamera',
            'seeing' => 'Nyugodtság',
            'transparency' => 'Átlátszóság',
            'location' => 'Helyszín',
            'date' => 'Időpont',
            'observer_id' => 'Feltöltő',
            'description' => 'Leírás',
            'uploaded_at' => 'Feltöltve',
            'edited_at' => 'Szerkesztve',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObserver()
    {
        return $this->hasOne(User::class, ['id' => 'observer_id']);
    }

    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->uploaded_at = new Expression("now()");
        }

        $this->edited_at = new Expression("now()");

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
    */
    public function getImage()
    {
        return $this->hasOne(Image::class, ['observe_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ObservesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ObservesQuery(get_called_class());
    }
}
