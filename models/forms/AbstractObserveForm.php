<?php

namespace app\models\forms;

use app\models\Image;
use yii\base\Model;
use yii\db\Exception;
use yii\web\UploadedFile;

class AbstractObserveForm extends Model
{
    public $id;

    public $object_name;
    public $location;
    public $description;
    public $date;
    public $transparency;
    public $seeing;
    public $mechanics;



    /** @var UploadedFile */
    public $image;

    public function rules() {
        return [
            [['object_name', 'telescope', 'location', 'description', 'date'], 'required', 'message' => "A mezőt kötelező kitölteni!"],
            [['object_name', 'telescope', 'location', 'description', 'mechanics'], 'string'],
            [['date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['image'], 'file', 'extensions' => 'jpg, jpeg, gif, png'],
            [['seeing', 'transparency'], 'default', 'value' => null],
            [['seeing', 'transparency'], 'integer'],
            [['seeing' ], 'in', 'range' => ['min' => 1, 'max' => 10]],
            [['transparency' ], 'in', 'range' => ['min' => 1, 'max' => 5]],
        ];
    }

    /**
     * @param $id int
     * @throws Exception
     */
    public function uploadImage($id)
    {
        if ($this->image != null) {
            $path = "uploads/" . $id . "." . $this->image->extension;

            if (!$this->image->saveAs($path) ){
                throw new \Exception("A kép feltöltése nem sikerült.");
            }
            $image = new Image();

            $image->observe_id = $id;
            $image->path = "/" . $path;

            if (!$image->save()) {
                throw new Exception("A kép mentése nem sikerült.");
            }
        }
    }
}