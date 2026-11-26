<?php

namespace app\models\forms;

use app\models\Activity;
use app\models\User;
use yii\base\Model;

/**
 * Sets or deletes the place the recommender calculates with for a user. The coordinates are rounded to about a
 * kilometer: it is plenty for the sky, and the exact address of the user is not stored.
 */
class LocationForm extends Model
{
    /** Decimals of the stored coordinates, 0.01° is about 1 km */
    const PRECISION = 2;

    /** @var string */
    public $name;
    /** @var float */
    public $latitude;
    /** @var float */
    public $longitude;

    public function rules()
    {
        return [
            [['name'], 'trim'],
            [['latitude', 'longitude'], 'required', 'message' => 'Válassz helyszínt a fenti lehetőségek egyikével!'],
            [['name'], 'required', 'message' => 'Adj nevet a helyszínnek!'],
            [['name'], 'string', 'max' => 255],
            [['latitude'], 'number', 'min' => -90, 'max' => 90],
            [['longitude'], 'number', 'min' => -180, 'max' => 180],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'A helyszín neve',
            'latitude' => 'Földrajzi szélesség',
            'longitude' => 'Földrajzi hosszúság',
        ];
    }

    /**
     * @param User $user
     * @return self filled with the saved place of the user
     */
    public static function fromUser(User $user)
    {
        $form = new self();
        $location = $user->getLocation();

        if ($location !== null) {
            $form->name = $location['name'];
            $form->latitude = $location['latitude'];
            $form->longitude = $location['longitude'];
        }

        return $form;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function save(User $user)
    {
        if (!$this->validate()) {
            return false;
        }

        $user->location_name = $this->name;
        $user->location_latitude = round((float)$this->latitude, self::PRECISION);
        $user->location_longitude = round((float)$this->longitude, self::PRECISION);

        if (!$user->save(false, ['location_name', 'location_latitude', 'location_longitude'])) {
            return false;
        }

        Activity::log($user->id, Activity::TYPE_LOCATION_CHANGE);

        return true;
    }

    /**
     * @param User $user
     */
    public static function delete(User $user)
    {
        $user->location_name = null;
        $user->location_latitude = null;
        $user->location_longitude = null;
        $user->save(false, ['location_name', 'location_latitude', 'location_longitude']);

        Activity::log($user->id, Activity::TYPE_LOCATION_DELETE);
    }
}
