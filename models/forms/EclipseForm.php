<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\Image;
use app\models\Observe;
use Yii;
use yii\db\Exception;
use yii\web\UploadedFile;

class EclipseForm extends AbstractObserveForm
{
    public $source;
    public $type = Observe::TYPE_ECLIPSE;
    public $duration;
    public $coverage;

    /*
      * TODO:
      *  -datum helyett pontos ido kell
      *  -valahol megadni, hogy leírásban add meg a nagyítást
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
        return array_merge(
            parent::attributeLabels(),
            [
            'coverage' => 'Fedettség',
            'duration' => 'Időtartam',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['duration', 'coverage', 'type'], 'string', 'max' => 255],
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
        $observe->duration = $this->duration;
        $observe->coverage = $this->coverage;
        $observe->filter = $this->filter;
        $observe->expo = $this->expo;

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
