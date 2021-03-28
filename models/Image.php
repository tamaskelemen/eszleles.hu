<?php

namespace app\models;

use app\models\Observe;
use Yii;

/**
 * This is the model class for table "images".
 *
 * @property int $id
 * @property string $path
 * @property int $observe_id
 * @property string $created_at
 * @property string $size
 */
class Image extends \yii\db\ActiveRecord
{
    const SUPPORTED_EXTENSIONS = 'jpg, jpeg, gif, png';

    const THUMBNAIL_WIDTH = 300;
    const THUMBNAIL_HEIGHT = 200;

    const SIZE_ORIGINAL = "original";
    const SIZE_THUMBNAIL = "thumbnail";

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['path', 'observe_id'], 'required'],
            [['observe_id'], 'default', 'value' => null],
            [['observe_id'], 'integer'],
            [['created_at'], 'safe'],
            [['path', 'size'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'path' => 'Path',
            'observe_id' => 'Észlelés',
            'created_at' => 'Feltöltve',
            'size' => 'Méret',
        ];
    }

    /**
     * @return mixed
     */
    public static function getUploadSizeLimit()
    {
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));

        return min($max_upload, $max_post, $memory_limit);
    }

    public static function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 100){
        $quality = 10;
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];
        $mime = $imgsize['mime'];

        switch($mime){
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image = "imagegif";
                break;

            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image = "imagepng";
                $quality = 9;
                break;

            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image = "imagejpeg";
                $quality = 100;
                break;



            default:
                return false;
                break;
        }

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $src_img = $image_create($source_file);

        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if($width_new > $width){
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        }else{
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if($dst_img)imagedestroy($dst_img);
        if($src_img)imagedestroy($src_img);
    }

    public function getObserve()
    {
        return $this->hasOne(Observe::class, ['id' => 'observe_id']);
    }

    /**
     * {@inheritdoc}
     * @return ImagesQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ImagesQuery(get_called_class());
    }
}
