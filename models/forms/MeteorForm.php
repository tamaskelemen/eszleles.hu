<?php
namespace app\models\forms;

use app\components\Flash;
use app\models\Observe;
use Yii;
use yii\db\Exception;
use yii\web\UploadedFile;

class MeteorForm extends AbstractObserveForm
{
    public $type = Observe::TYPE_METEOR;

    public $meteor_membership;
    public $brightness;
    public $color;

    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [['meteor_membership', 'color'], 'string',],
                [['brightness'], 'double', 'message' => "Kérjük, számot adjon meg fényességnek!"],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'meteor_membership' => 'Rajtagság',
                'color' => 'Szín',
                'brightness' => 'Fényesség',
            ]
        );
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
        $observe->meteor_membership = $this->meteor_membership;
        $observe->color = $this->color;
        $observe->brightness = $this->brightness;
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