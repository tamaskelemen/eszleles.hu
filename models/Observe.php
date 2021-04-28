<?php

namespace app\models;

use app\components\Helper;
use Yii;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * This is the model class for table "observations".
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
 * @property string $mechanics
 * @property double $moon_phase
 * @property string $meteor_membership
 * @property double $brightness
 * @property string $color
 * @property string $expo
 * @property string $filter
 *
 * @property User $observer
 */
class Observe extends \yii\db\ActiveRecord
{
    const TYPE_DEEP_SKY = "deepsky";
    const TYPE_MOON = "moon";
    const TYPE_PLANET = "planet";
    const TYPE_METEOR = "meteor";
    const TYPE_LANDSCAPE = "landscape";
    const TYPE_COMET = "comet";
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'observations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['object_name', 'telescope', 'location', 'description', 'date'], 'required', 'message' => "A mezőt kötelező kitölteni!"],
            [['seeing', 'transparency', 'observer_id'], 'default', 'value' => null],
            [['seeing', 'transparency', 'observer_id'], 'integer'],
            [['seeing'], 'in', 'range' => range(0, 10)],
            [['transparency'], 'in', 'range' => range(0, 5)],
            [['date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['uploaded_at', 'edited_at'], 'safe'],
            [['description', 'type', 'mechanics', 'color', 'filter', 'expo'], 'string'],
            [['object_name', 'catalog_number', 'constellation', 'object_type', 'telescope', 'camera', 'location', 'meteor_membership'], 'string', 'max' => 255],
            [['observer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['observer_id' => 'id']],
            [['moon_phase', 'brightness'], 'double']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_name' => 'Objektum neve',
            'catalog_number' => 'Katalógus szám',
            'constellation' => 'Csillagkép',
            'object_type' => 'Objektum típus',
            'type' => 'Típus',
            'telescope' => 'Távcső',
            'camera' => 'Kamera',
            'mechanics' => 'Mechanika',
            'moon_phase' => 'Hold fázis',
            'seeing' => 'Nyugodtság',
            'transparency' => 'Átlátszóság',
            'location' => 'Helyszín',
            'date' => 'Időpont',
            'observer_id' => 'Feltöltő',
            'description' => 'Leírás',
            'uploaded_at' => 'Feltöltve',
            'edited_at' => 'Szerkesztve',
            'meteor_membership' => 'Rajtagság',
            'brightness' => 'Fényesség',
            'color' => 'Szín',
            'expo' => 'Expozíciós adatok',
            'filter' => 'Szűrők',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getObserver()
    {
        return $this->hasOne(User::class, ['id' => 'observer_id']);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::class, ['observation_id' => 'id']);
    }
//    /**
//     * @return Comment[]|array
//     */
//    public function getComments()
//    {
//        return Comment::find()->where(['observation_id' => $this->id])->orderBy(['created_at' => SORT_DESC])->all();
//    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($this->isNewRecord) {
            $this->uploaded_at = new Expression("NOW()");
        }

        $this->edited_at = new Expression("NOW()");

        return parent::beforeSave($insert);
    }

    public static function getVisibleAttributes()
    {
        return [
            'telescope',
            'date',
            'location',
            'transparency',
            'seeing',
            'camera',
            'mechanics',
            'expo',
            'filter',
        ];
    }

    public static function getAllTypes()
    {
        return [
            self::TYPE_MOON => 'Hold',
            self::TYPE_DEEP_SKY => 'Mélyég',
            self::TYPE_PLANET => 'Bolygó',
            self::TYPE_METEOR => 'Meteor',
            self::TYPE_LANDSCAPE => 'Asztrotájkép',
            self::TYPE_COMET => 'Üstökös',
        ];
    }

    public static function getAllHelptext()
    {
        return [
            'telescope' => 'Objektív vagy távcső',
            'date' => 'Az észlelés dátuma',
            'location' => 'Helyszín, ahol a megfigyelés történt',
            'transparency' => 'A légkör átlátszósága egy 0-10 skálán',
            'seeing' => 'A légkör nyugodtsága egy 0-10 skálán',
            'mechanics' => 'A mechanika amit használtál a távcsöved alatt',
            'filter' => 'Mély-ég vagy egyéb szűrők',
            'expo' => 'ISO érték, rekeszérték, záridő, hány darab kép',
            'camera' => 'Kamera vagy szenzor, amivel a kép készült',
        ];
    }

    public static function getAttribteHelpText($attribute) {
        return self::getAllHelptext()[$attribute] ?? "";
    }

    /**
     * @param string $name
     */
    public static function getTypeName(string $name)
    {
        return self::getAllTypes()[$name];
    }
    /**
     * @return \yii\db\ActiveQuery
    */
    public function getImage()
    {
        return $this->hasOne(Image::class, ['observe_id' => 'id'])->where(['size'=> Image::SIZE_ORIGINAL]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getThumbnail()
    {
        return $this->hasOne(Image::class, ['observe_id' => 'id'])->where(['size'=> Image::SIZE_THUMBNAIL]);
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        if ($this->image != null) {
            return $this->image->path;
        }

        return "/pictures/noimage.png";
    }

    /**
     * @return string
     */
    public function getThumbnailPath()
    {
        if ($this->thumbnail != null ) {
            return $this->thumbnail->path;
        }

        return "/pictures/noimage.png";
    }

    /**
     * @return string
     */
    public function getViewUrl()
    {
        return Url::to(["/" . $this->type . "/view", "id" => $this->id]);
    }

    public function generateUrl()
    {
        $url = "";
        $url .= Yii::$app->getHomeUrl();
        $url .= "/";
        $type = self::getTypeName($this->type);
        $url .= lcfirst(Helper::replaceUnaccent($type));
        $url .= "/";
        $url .= $this->id;
        $url .= "-";
        $title = Helper::replaceUnaccent($this->object_name);
        $title = urlencode($title);
        $url .=  str_replace(' ', '_', $title);

        return $url;
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
