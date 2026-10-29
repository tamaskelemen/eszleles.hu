<?php

namespace app\models\forms;

use app\components\Flash;
use app\models\Image;
use app\models\ImageVersion;
use app\models\Observe;
use Yii;
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
            [['seeing' ], 'in', 'range' => range(1, 10)],
            [['transparency' ], 'in', 'range' => range(1, 5)],
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
     * Uploads the image as a new image version of the observation. The latest version is the main image.
     *
     * @param $id int id of the observation the image belongs to
     * @throws Exception
     */
    public function uploadImage($id)
    {
        if ($this->image != null) {
            if (!$this->validate(['image'])) {
                throw new \Exception($this->getFirstError('image'));
            }

            $transaction = Yii::$app->db->beginTransaction();
            $fileName = null;

            try {
                $version = new ImageVersion();
                $version->observe_id = $id;

                if (!$version->save()) {
                    throw new Exception("A képverzió mentése nem sikerült.");
                }

                $fileName = $id . "_" . $version->id . "." . $this->image->extension;
                $path = "uploads/" . $fileName;

                if (!$this->image->saveAs($path) ){
                    throw new \Exception("A kép feltöltése nem sikerült.");
                }
                $image = new Image();
                $image->observe_id = $id;
                $image->image_version_id = $version->id;
                $image->path = "/" . $path;
                $image->size = Image::SIZE_ORIGINAL;

                if (!$image->save()) {
                    throw new Exception("A kép mentése nem sikerült.");
                }

                $this->createThumbnail($id, $version->id, $path, $fileName);

                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();

                // do not leave files on disk that no image row references
                if ($fileName !== null) {
                    @unlink("uploads/" . $fileName);
                    @unlink("uploads/thumbnails/" . $fileName);
                }

                throw $e;
            }
        }
    }

    /**
     * Saves an already loaded observation and, if a new image was posted, adds it as a new image version.
     *
     * @param Observe $observe
     * @return bool
     */
    public function saveObservation(Observe $observe)
    {
        $this->image = UploadedFile::getInstance($observe, 'image');

        $transaction = Yii::$app->db->beginTransaction();

        try {
            if (!$observe->save()) {
                $transaction->rollBack();
                return false;
            }

            $this->uploadImage($observe->id);

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Flash::addDanger($e->getMessage());
            return false;
        }
    }

    private function createThumbnail($id, $versionId, $source, $fileName)
    {
        $destination = "uploads/thumbnails/" . $fileName;
        Image::resize_crop_image(
            Image::THUMBNAIL_WIDTH, Image::THUMBNAIL_HEIGHT,
            $source,
            $destination
        );

        $thumbnail = new Image();
        $thumbnail->observe_id = $id;
        $thumbnail->image_version_id = $versionId;
        $thumbnail->path = "/" . $destination;
        $thumbnail->size = Image::SIZE_THUMBNAIL;

        if (!$thumbnail->save()) {
            throw new \Exception("Thumbnail mentése nem sikerült.");
        }

        return $this;
    }
}