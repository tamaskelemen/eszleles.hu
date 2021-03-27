<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\Image;
use app\models\Observe;
use Yii;
use yii\db\Exception;
use yii\web\UploadedFile;

class MoonForm extends AbstractObserveForm
{
    public $telescope;
    public $camera;
    public $source;
    public $type = Observe::TYPE_MOON;
    public $moon_phase;

    /*
      * TODO:
      *  -datum helyett pontos ido kell
      *  -csillagkép, tipust ki kell venni
      *  -valahol megadni, hogy leírásban add meg a nagyítást
      *  -hold fázist meg kell adni
      *
      * -feature request:
      *  több nagyítás? több okulár miatt
      *   libráció?
      *
      */
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'object_name' => 'Objektum neve',
            'object_type' => 'Típus',
            'telescope' => 'Távcső',
            'mechanics' => 'Mechanika',
            'moon_phase' => 'Holdfázis',
            'camera' => 'Kamera',
            'seeing' => 'Nyugodtság',
            'transparency' => 'Átlátszóság',
            'location' => 'Helyszín',
            'date' => 'Időpont',
            'description' => 'Leírás',
            'image' => 'Kép',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['moon_phase', 'telescope', 'camera', 'type'], 'string', 'max' => 255],
        ];

        return array_merge(parent::rules(), $rules);
    }

    /**
     * @return bool
     */
    public function register()
    {
        $this->image = UploadedFile::getInstance($this, 'image');

        if (!$this->validate()) {
            return false;
        }

        $observe = new Observe();

        $observe->observer_id = Yii::$app->user->id;
        $observe->object_name = $this->object_name;
        $observe->telescope = $this->telescope;
        $observe->location = $this->location;
        $observe->mechanics = $this->mechanics;
        $observe->camera = $this->camera;
        $observe->seeing = $this->seeing;
        $observe->transparency = $this->transparency;
        $observe->date = $this->date;
        $observe->description = $this->description;
        $observe->type = $this->type;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$observe->save()) {
                throw new Exception("Az észlelés feltöltése nem sikerült.");
            }

            $this->uploadImage($observe->id);

            $this->id = $observe->id;

            $transaction->commit();

            return true;

        } catch (\Exception $e) {
            Flash::addDanger($e->getMessage());
            $transaction->rollBack();
            return false;
        }
    }
}
