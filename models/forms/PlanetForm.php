<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\Image;
use app\models\Observe;
use Yii;
use yii\db\Exception;
use yii\web\UploadedFile;

class PlanetForm extends AbstractObserveForm
{

    public $object_type;
    public $type  = Observe::TYPE_PLANET;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
            'object_type' => 'Típus',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['object_type', 'type'], 'string', 'max' => 255],
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
