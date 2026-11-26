<?php

namespace app\components;

/**
 * Low precision positional astronomy, good to a few arc minutes for the Sun and about half a degree for the Moon,
 * which is plenty for telling what is above the horizon. Angles are in degrees, longitudes are positive to the east.
 * Formulas of the Astronomical Almanac (low precision) and Meeus: Astronomical Algorithms.
 */
class Astro
{
    /** Sidereal days are shorter, solar hours per sidereal hour */
    const SIDEREAL_RATE = 1.00273790935;

    /**
     * @param int $timestamp unix time
     * @return float days since J2000.0
     */
    public static function daysSinceJ2000($timestamp)
    {
        return $timestamp / 86400 + 2440587.5 - 2451545.0;
    }

    /**
     * @param int $timestamp
     * @param float $longitude
     * @return float local sidereal time in degrees
     */
    public static function localSiderealTime($timestamp, $longitude)
    {
        $d = self::daysSinceJ2000($timestamp);
        $t = $d / 36525;

        return self::normalize(280.46061837 + 360.98564736629 * $d + 0.000387933 * $t * $t + $longitude);
    }

    /**
     * @param int $timestamp
     * @return array ra, dec, longitude (ecliptic)
     */
    public static function sun($timestamp)
    {
        $d = self::daysSinceJ2000($timestamp);
        $g = deg2rad(self::normalize(357.528 + 0.9856003 * $d));
        $longitude = self::normalize(280.460 + 0.9856474 * $d + 1.915 * sin($g) + 0.020 * sin(2 * $g));

        return self::eclipticToEquatorial($longitude, 0, $d) + ['longitude' => $longitude];
    }

    /**
     * Geocentric position of the Moon.
     *
     * @param int $timestamp
     * @return array ra, dec, longitude (ecliptic)
     */
    public static function moon($timestamp)
    {
        $d = self::daysSinceJ2000($timestamp);
        $t = $d / 36525;
        $sin = function ($a) {
            return sin(deg2rad($a));
        };

        $longitude = 218.32 + 481267.881 * $t
            + 6.29 * $sin(135.0 + 477198.87 * $t) - 1.27 * $sin(259.3 - 413335.36 * $t)
            + 0.66 * $sin(235.7 + 890534.22 * $t) + 0.21 * $sin(269.9 + 954397.74 * $t)
            - 0.19 * $sin(357.5 + 35999.05 * $t) - 0.11 * $sin(186.5 + 966404.03 * $t);
        $latitude = 5.13 * $sin(93.3 + 483202.02 * $t) + 0.28 * $sin(228.2 + 960400.89 * $t)
            - 0.28 * $sin(318.3 + 6003.15 * $t) - 0.17 * $sin(217.6 - 407332.21 * $t);
        $longitude = self::normalize($longitude);

        return self::eclipticToEquatorial($longitude, $latitude, $d) + ['longitude' => $longitude];
    }

    /**
     * @param int $timestamp
     * @return array fraction (illuminated, 0…1) and isWaxing
     */
    public static function moonPhase($timestamp)
    {
        $sun = self::sun($timestamp);
        $moon = self::moon($timestamp);
        $elongation = self::distance($sun['ra'], $sun['dec'], $moon['ra'], $moon['dec']);

        return [
            'fraction' => (1 - cos(deg2rad($elongation))) / 2,
            'isWaxing' => self::normalize($moon['longitude'] - $sun['longitude']) < 180,
        ];
    }

    /**
     * @param float $ra
     * @param float $dec
     * @param float $latitude
     * @param float $lst local sidereal time
     * @return array altitude, azimuth (from the north to the east)
     */
    public static function horizontal($ra, $dec, $latitude, $lst)
    {
        $h = deg2rad(self::hourAngle($ra, $lst));
        $dec = deg2rad($dec);
        $lat = deg2rad($latitude);

        $altitude = asin(sin($dec) * sin($lat) + cos($dec) * cos($lat) * cos($h));
        $azimuth = atan2(sin($h), cos($h) * sin($lat) - tan($dec) * cos($lat));

        return [
            'altitude' => rad2deg($altitude),
            'azimuth' => self::normalize(rad2deg($azimuth) + 180),
        ];
    }

    /**
     * @param float $ra
     * @param float $lst
     * @return float hour angle in degrees, 0…360, below 180 the object is past the meridian
     */
    public static function hourAngle($ra, $lst)
    {
        return self::normalize($lst - $ra);
    }

    /**
     * @param float $dec
     * @param float $latitude
     * @return float altitude at the upper culmination
     */
    public static function meridianAltitude($dec, $latitude)
    {
        return 90 - abs($latitude - $dec);
    }

    /**
     * @param float $ra
     * @param float $lst
     * @param int $timestamp now
     * @return array previous and next upper culmination, unix times
     */
    public static function transits($ra, $lst, $timestamp)
    {
        $sinceHours = self::hourAngle($ra, $lst) / 15 / self::SIDEREAL_RATE;
        $dayHours = 24 / self::SIDEREAL_RATE;

        return [
            'previous' => (int)round($timestamp - $sinceHours * 3600),
            'next' => (int)round($timestamp + ($dayHours - $sinceHours) * 3600),
        ];
    }

    /**
     * @return float angular distance in degrees
     */
    public static function distance($ra1, $dec1, $ra2, $dec2)
    {
        $cos = sin(deg2rad($dec1)) * sin(deg2rad($dec2)) + cos(deg2rad($dec1)) * cos(deg2rad($dec2)) * cos(deg2rad($ra1 - $ra2));

        return rad2deg(acos(max(-1, min(1, $cos))));
    }

    /**
     * @param float $altitude of the Sun
     * @return string
     */
    public static function getTwilightName($altitude)
    {
        if ($altitude > 0) {
            return 'nappal';
        }
        if ($altitude > -6) {
            return 'polgári szürkület';
        }
        if ($altitude > -12) {
            return 'navigációs szürkület';
        }
        if ($altitude > -18) {
            return 'csillagászati szürkület';
        }

        return 'sötét éjszaka';
    }

    /**
     * @param float $azimuth
     * @return string Hungarian compass point, e.g. "ÉK"
     */
    public static function getDirectionName($azimuth)
    {
        $names = ['É', 'ÉÉK', 'ÉK', 'KÉK', 'K', 'KDK', 'DK', 'DDK', 'D', 'DDNy', 'DNy', 'NyDNy', 'Ny', 'NyÉNy', 'ÉNy', 'ÉÉNy'];

        return $names[(int)round(self::normalize($azimuth) / 22.5) % 16];
    }

    /**
     * @param float $ra degrees
     * @return string e.g. "05h 35m 17s"
     */
    public static function formatRa($ra)
    {
        $seconds = (int)round(self::normalize($ra) / 15 * 3600) % 86400;

        return sprintf('%02dh %02dm %02ds', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    }

    /**
     * @param float $dec degrees
     * @return string e.g. "−05° 23′"
     */
    public static function formatDec($dec)
    {
        $minutes = (int)round(abs($dec) * 60);

        return ($dec < 0 ? '−' : '+') . sprintf('%02d° %02d′', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * @param float $angle
     * @return float 0…360
     */
    public static function normalize($angle)
    {
        $angle = fmod($angle, 360);

        return $angle < 0 ? $angle + 360 : $angle;
    }

    private static function eclipticToEquatorial($longitude, $latitude, $d)
    {
        $e = deg2rad(23.439 - 0.0000004 * $d);
        $l = deg2rad($longitude);
        $b = deg2rad($latitude);

        $ra = atan2(sin($l) * cos($e) - tan($b) * sin($e), cos($l));
        $dec = asin(sin($b) * cos($e) + cos($b) * sin($e) * sin($l));

        return ['ra' => self::normalize(rad2deg($ra)), 'dec' => rad2deg($dec)];
    }
}
