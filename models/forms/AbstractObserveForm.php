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
    public $telescope;
    public $mechanics;
    public $camera;
    public $expo;
    public $filter;

    /** @var UploadedFile */
    public $image;

    public function rules() {
        return [
            [['object_name', 'telescope', 'location', 'description', 'date'], 'required', 'message' => "A mezőt kötelező kitölteni!"],
            [['object_name', 'telescope', 'location', 'description', 'mechanics', 'camera', 'filter', 'expo'], 'string'],
            [['date'], 'date', 'format' => 'yyyy-MM-dd'],
            [['image'], 'file', 'extensions' => Image::SUPPORTED_EXTENSIONS],
            [['seeing', 'transparency'], 'default', 'value' => null],
            [['seeing', 'transparency'], 'integer'],
            [['seeing' ], 'in', 'range' => ['min' => 1, 'max' => 10]],
            [['transparency' ], 'in', 'range' => ['min' => 1, 'max' => 5]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'object_name' => 'Objektum neve',
            'location' => 'Helyszín',
            'date' => 'Időpont',
            'description' => 'Leírás',
            'image' => 'Kép',
            'seeing' => 'Nyugodtság',
            'transparency' => 'Átlátszóság',
            'camera' => 'Kamera',
            'mechanics' => 'Mechanika',
            'telescope' => 'Optika',
            'filter' => 'Szűrők',
            'expo' => 'Expozíciós adatok',
        ];
    }

    /**
     * @param $id int id of the observation the image belongs to
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
            $image->size = Image::SIZE_ORIGINAL;

            if (!$image->save()) {
                throw new Exception("A kép mentése nem sikerült.");
            }

            $this->createThumbnail($id, $path);
        }
    }

    private function createThumbnail($id, $source)
    {
        $destination = "uploads/thumbnails/{$id}.{$this->image->extension}";
        Image::resize_crop_image(
            Image::THUMBNAIL_WIDTH, Image::THUMBNAIL_HEIGHT,
            $source,
            $destination
        );

        $thumbnail = new Image();
        $thumbnail->observe_id = $id;
        $thumbnail->path = "/" . $destination;
        $thumbnail->size = Image::SIZE_THUMBNAIL;

        if (!$thumbnail->save()) {
            throw new \Exception("Thumbnail mentése nem sikerült.");
        }

        return $this;
    }
}