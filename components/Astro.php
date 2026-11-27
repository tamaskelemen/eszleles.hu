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

    /** The upper limb touches the horizon: semidiameter and refraction */
    const SUNSET_ALTITUDE = -0.833;
    /** The end of the astronomical twilight */
    const DARK_ALTITUDE = -18;
    /** Coarse scan step of the crossing search, in seconds, short enough not to skip a crossing */
    const CROSSING_STEP = 600;

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
     * @param int $timestamp
     * @param float $latitude
     * @param float $longitude
     * @return float altitude of the Sun's center, without refraction
     */
    public static function sunAltitude($timestamp, $latitude, $longitude)
    {
        $sun = self::sun($timestamp);

        return self::horizontal($sun['ra'], $sun['dec'], $latitude, self::localSiderealTime($timestamp, $longitude))['altitude'];
    }

    /**
     * The night in progress, or the coming one while the Sun is up. The astronomical darkness is the part of it when
     * the Sun is below -18°, it is missing on short summer nights in the north.
     *
     * @param float $latitude
     * @param float $longitude
     * @param int|null $timestamp now if null
     * @return array sunset, sunrise, darkBegin, darkEnd as unix times, any of them null if it does not happen
     */
    public static function night($latitude, $longitude, $timestamp = null)
    {
        $now = $timestamp ?? time();
        $altitude = function ($t) use ($latitude, $longitude) {
            return self::sunAltitude($t, $latitude, $longitude);
        };
        $day = 86400;

        if ($altitude($now) < self::SUNSET_ALTITUDE) {
            $sunset = self::lastCrossing($altitude, self::SUNSET_ALTITUDE, false, $now - $day, $now);
            $sunrise = self::crossing($altitude, self::SUNSET_ALTITUDE, true, $now, $now + $day);
        } else {
            $sunset = self::crossing($altitude, self::SUNSET_ALTITUDE, false, $now, $now + $day);
            $sunrise = $sunset === null ? null : self::crossing($altitude, self::SUNSET_ALTITUDE, true, $sunset, $sunset + $day);
        }

        $darkBegin = $darkEnd = null;
        if ($sunset !== null && $sunrise !== null) {
            $darkBegin = self::crossing($altitude, self::DARK_ALTITUDE, false, $sunset, $sunrise);
            $darkEnd = $darkBegin === null ? null : self::crossing($altitude, self::DARK_ALTITUDE, true, $darkBegin, $sunrise);
        }

        return ['sunset' => $sunset, 'sunrise' => $sunrise, 'darkBegin' => $darkBegin, 'darkEnd' => $darkEnd];
    }


    /**
     * @param callable $altitude of a unix time
     * @param float $limit
     * @param bool $rising crossing upwards or downwards
     * @param int $from
     * @param int $to
     * @return int|null the first time between the two when the altitude crosses the limit
     */
    private static function crossing($altitude, $limit, $rising, $from, $to)
    {
        $previousTime = $from;
        $previous = $altitude($from);
        while ($previousTime < $to) {
            $t = min($previousTime + self::CROSSING_STEP, $to);
            $current = $altitude($t);
            if ($rising ? ($previous < $limit && $current >= $limit) : ($previous >= $limit && $current < $limit)) {
                return self::bisect($altitude, $limit, $rising, $previousTime, $t);
            }
            $previousTime = $t;
            $previous = $current;
        }

        return null;
    }

    /**
     * @see crossing() the same, but the last one
     */
    private static function lastCrossing($altitude, $limit, $rising, $from, $to)
    {
        $last = null;
        while (($found = self::crossing($altitude, $limit, $rising, $from, $to)) !== null) {
            $last = $found;
            $from = $found + 1;
        }

        return $last;
    }

    private static function bisect($altitude, $limit, $rising, $from, $to)
    {
        while ($to - $from > 30) {
            $middle = intdiv($from + $to, 2);
            $isAbove = $altitude($middle) >= $limit;
            if ($isAbove === $rising) {
                $to = $middle;
            } else {
                $from = $middle;
            }
        }

        return $to;
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
