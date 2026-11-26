<?php

namespace app\components\recommender;

use app\components\Astro;

/**
 * Where, when and for whom the recommendation is made. Everything that depends on the time and the place is
 * calculated here once, the filters read it.
 */
class Context
{
    /** Used for guests and when the location of a user is not known */
    const DEFAULT_PLACE = ['name' => 'Budapest', 'latitude' => 47.4979, 'longitude' => 19.0402];

    /** The Moon is 60 Earth radii away, so its parallax is about this much, in degrees */
    const MOON_PARALLAX = 0.95;

    /** @var float */
    public $latitude;
    /** @var float positive to the east */
    public $longitude;
    /** @var string */
    public $placeName;
    /** @var bool whether DEFAULT_PLACE is used, because no other was given */
    public $isDefaultPlace;
    /** @var int unix time */
    public $timestamp;
    /** @var int|null */
    public $userId;
    /** @var float local sidereal time in degrees */
    public $lst;
    /** @var float */
    public $sunAltitude;
    /** @var array ra, dec, altitude, azimuth, fraction, isWaxing */
    public $moon;

    /**
     * @param int|null $userId null for guests
     * @param array|null $place name, latitude, longitude, the default one if null
     * @param int|null $timestamp now if null
     */
    public function __construct($userId = null, $place = null, $timestamp = null)
    {
        $this->isDefaultPlace = $place === null;
        $place = $place ?? self::DEFAULT_PLACE;

        $this->userId = $userId;
        $this->placeName = (string)$place['name'];
        $this->latitude = (float)$place['latitude'];
        $this->longitude = (float)$place['longitude'];
        $this->timestamp = $timestamp ?? time();
        $this->lst = Astro::localSiderealTime($this->timestamp, $this->longitude);

        $sun = Astro::sun($this->timestamp);
        $this->sunAltitude = Astro::horizontal($sun['ra'], $sun['dec'], $this->latitude, $this->lst)['altitude'];

        $moon = Astro::moon($this->timestamp);
        $position = Astro::horizontal($moon['ra'], $moon['dec'], $this->latitude, $this->lst);
        // seen from the surface the Moon is lower than from the center of the Earth
        $position['altitude'] -= self::MOON_PARALLAX * cos(deg2rad($position['altitude']));
        $this->moon = $moon + $position + Astro::moonPhase($this->timestamp);
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->userId === null;
    }

    /**
     * @param string $alias of the sky_objects table
     * @return string SQL expression of the altitude of the objects now, in degrees
     */
    public function altitudeSql($alias = 'o')
    {
        return sprintf(
            'degrees(asin(LEAST(1, sind(%1$s.dec) * sind(%2$F) + cosd(%1$s.dec) * cosd(%2$F) * cosd(%3$F - %1$s.ra))))',
            $alias,
            $this->latitude,
            $this->lst
        );
    }

    /**
     * @param float $ra
     * @param float $dec
     * @param string $alias of the sky_objects table
     * @return string SQL expression of the angular distance of the objects from the given point, in degrees
     */
    public function distanceSql($ra, $dec, $alias = 'o')
    {
        return sprintf(
            'degrees(acos(LEAST(1, sind(%1$s.dec) * sind(%3$F) + cosd(%1$s.dec) * cosd(%3$F) * cosd(%1$s.ra - %2$F))))',
            $alias,
            $ra,
            $dec
        );
    }
}
