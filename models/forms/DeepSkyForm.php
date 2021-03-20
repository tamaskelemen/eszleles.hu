<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\Image;
use app\models\Observe;
use Yii;
use yii\db\Exception;
use yii\web\UploadedFile;

class DeepSkyForm extends \yii\base\Model
{
    /** @var UploadedFile */
    public $image;

    public $id;

    public $catalog_number;
    public $constellation;
    public $object_type;
    public $telescope;
    public $camera;
    public $seeing;
    public $transparency;
    public $location;
    public $source;
    public $date;
    public $description;
    public $type  = Observe::TYPE_DEEP_SKY;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'catalog_number' => 'Objektum neve',
            'constellation' => 'Csillagkép',
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
        return [
            [['image'], 'file', 'extensions' => 'jpg, jpeg, gif, png'],
            [['catalog_number', 'constellation', 'telescope', 'location', 'description'], 'required', 'message' => "A mezőt kötelező kitölteni!"],
            [['seeing', 'transparency'], 'default', 'value' => null],
            [['seeing', 'transparency'], 'integer'],
            [['seeing' ], 'in', 'range' => ['min' => 1, 'max' => 10]],
            [['transparency' ], 'in', 'range' => ['min' => 1, 'max' => 5]],
            [['date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['description'], 'string'],
            [['catalog_number', 'constellation', 'object_type', 'telescope', 'camera', 'location', 'type'], 'string', 'max' => 255],
        ];
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
        $observe->catalog_number = $this->catalog_number;
        $observe->constellation = $this->constellation;
        $observe->telescope = $this->telescope;
        $observe->location = $this->location;
        $observe->object_type = $this->object_type;
        $observe->camera = $this->camera;
        $observe->seeing = $this->seeing;
        $observe->transparency = $this->transparency;
        $observe->source = $this->source;
        $observe->date = $this->date;
        $observe->description = $this->description;
        $observe->type = $this->type;

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$observe->save()) {
                throw new Exception("Az észlelés feltöltése nem sikerült.");
            }

            $year = date("Y");
            $month = date('m');

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
