<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\Image;
use app\models\Observe;
use Yii;
use yii\db\Exception;
use yii\web\UploadedFile;

class DeepSkyForm extends AbstractObserveForm
{
    public $object_type;
    public $telescope;
    public $camera;
    public $seeing;
    public $transparency;
    public $type  = Observe::TYPE_DEEP_SKY;

    /*
     * TODO:
     *  -datum helyett pontos ido kell
     *  -csillagkép, tipust ki kell venni
     *  -valahol megadni, hogy leírásban add meg a nagyítást
     *
     * -feature request:
     *  több nagyítás? több okulár miatt
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
            [['seeing', 'transparency'], 'default', 'value' => null],
            [['seeing', 'transparency'], 'integer'],
            [['seeing' ], 'in', 'range' => ['min' => 1, 'max' => 10]],
            [['transparency' ], 'in', 'range' => ['min' => 1, 'max' => 5]],
            [['object_name', 'object_type', 'telescope', 'camera', 'location', 'type'], 'string', 'max' => 255],
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

            if ($this->image != null) {
                $path = "uploads/" . $observe->id . "." . $this->image->extension;

                if (!$this->image->saveAs($path) ){
                    throw new \Exception("A kép feltöltése nem sikerült.");
                }
                $image = new Image();

                $image->observe_id = $observe->id;
                $image->path = $path;

                if (!$image->save()) {
                    throw new Exception("A kép mentése nem sikerült.");
                }
            }

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
