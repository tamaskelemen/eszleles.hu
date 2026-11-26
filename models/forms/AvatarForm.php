<?php

namespace app\models\forms;

use app\models\Activity;
use app\models\Image;
use app\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\httpclient\Client;
use yii\web\UploadedFile;

/**
 * Uploads, replaces and deletes the profile picture of a user, or copies the picture of an external account.
 * The picture is cropped to a square.
 */
class AvatarForm extends Model
{
    const SIZE = 400;
    const DIRECTORY = 'uploads/avatars';
    // Image::resize_crop_image() can write these only
    const SUPPORTED_EXTENSIONS = 'jpg, jpeg, png';
    const FILE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

    /** @var UploadedFile */
    public $image;

    public function rules()
    {
        return [
            [['image'], 'required', 'message' => 'Válassz ki egy képet!'],
            [['image'], 'file',
                'extensions' => self::SUPPORTED_EXTENSIONS,
                'maxSize' => 10 * 1024 * 1024,
                'wrongExtension' => 'Csak JPG vagy PNG képet tölthetsz fel.',
                'tooBig' => 'A kép legfeljebb 10 MB lehet.',
            ],
        ];
    }

    public function attributeLabels()
    {
        return [
            'image' => 'Profilkép',
        ];
    }

    /**
     * Saves the posted image as the profile picture of the user, the previous one is deleted.
     *
     * @param User $user
     * @return bool
     */
    public function upload(User $user)
    {
        $this->image = UploadedFile::getInstance($this, 'image');

        if (!$this->validate()) {
            return false;
        }

        $hadAvatar = $user->avatar_path !== null;

        if (!self::store($user, $this->image->tempName, User::AVATAR_SOURCE_UPLOAD)) {
            $this->addError('image', 'A képet nem sikerült feldolgozni.');
            return false;
        }

        Activity::log($user->id, $hadAvatar ? Activity::TYPE_AVATAR_CHANGE : Activity::TYPE_AVATAR_UPLOAD);

        return true;
    }

    /**
     * Copies an external picture as the profile picture, unless the user uploaded or deleted one,
     * or it is copied already.
     *
     * @param User $user
     * @param string $source the id of the auth client
     * @param string|null $url
     * @throws \Exception
     */
    public static function copy(User $user, $source, $url)
    {
        if (!$url || strpos($url, 'https://') !== 0 || !$user->canCopyAvatar()
            || ($user->avatar_source === $source && $user->avatar_source_url === $url)
        ) {
            return;
        }

        $response = (new Client())->get(self::getLargeGoogleUrl($url))
            ->setOptions(['timeout' => 5])
            ->send();
        if (!$response->isOk) {
            throw new \Exception("A külső profilkép nem tölthető le: {$response->statusCode}.");
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'avatar');

        try {
            file_put_contents($tempFile, $response->content);

            if (!self::store($user, $tempFile, $source, $url)) {
                throw new \Exception("A külső profilképet nem sikerült feldolgozni.");
            }
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * @param User $user
     */
    public static function delete(User $user)
    {
        $previous = $user->avatar_path;
        $user->updateAttributes([
            'avatar_path' => null,
            'avatar_source' => User::AVATAR_SOURCE_REMOVED,
            'avatar_source_url' => null,
        ]);
        self::deleteFile($previous);

        if ($previous !== null) {
            Activity::log($user->id, Activity::TYPE_AVATAR_DELETE);
        }
    }

    /**
     * Crops the image to the profile picture of the user, the previous one is deleted.
     *
     * @param User $user
     * @param string $file
     * @param string $source
     * @param string|null $sourceUrl
     * @return bool
     * @throws \yii\base\Exception
     */
    private static function store(User $user, $file, $source, $sourceUrl = null)
    {
        $mime = @getimagesize($file)['mime'] ?? null;

        if (!isset(self::FILE_TYPES[$mime])) {
            return false;
        }

        $directory = Yii::getAlias('@webroot/' . self::DIRECTORY);
        FileHelper::createDirectory($directory);

        // a new name on every upload, so the browsers do not show the cached old picture
        $fileName = $user->id . '_' . time() . '.' . self::FILE_TYPES[$mime];

        if (Image::resize_crop_image(self::SIZE, self::SIZE, $file, "$directory/$fileName") === false) {
            return false;
        }

        $previous = $user->avatar_path;
        $user->updateAttributes([
            'avatar_path' => '/' . self::DIRECTORY . '/' . $fileName,
            'avatar_source' => $source,
            'avatar_source_url' => $sourceUrl,
        ]);

        if ($previous !== $user->avatar_path) {
            self::deleteFile($previous);
        }

        return true;
    }

    /**
     * Google gives a 96px picture by default, the size is in the end of the address (e.g. "=s96-c").
     *
     * @param string $url
     * @return string
     */
    private static function getLargeGoogleUrl($url)
    {
        if (strpos(parse_url($url, PHP_URL_HOST), 'googleusercontent.com') === false) {
            return $url;
        }

        return preg_replace('/=s\d+(-c)?$/', '=s' . self::SIZE . '-c', $url);
    }

    /**
     * @param string|null $path
     */
    private static function deleteFile($path)
    {
        if ($path && strpos($path, '/' . self::DIRECTORY . '/') === 0) {
            @unlink(Yii::getAlias('@webroot' . $path));
        }
    }
}
