<?php

namespace app\components;

class Data
{
    const TYPE_NYILTHALMAZ = 'NYH';
    const TYPE_GOMBHALMAZ = 'GH';
    const TYPE_DIFFUZ_KOD = 'DK';
    const TYPE_GALAXIS = 'GX';
    const TYPE_PLANETARIS_KOD = 'PL';
    const TYPE_CSILLAGKOD = 'NL';
    const TYPE_SZUPERNOVA = 'SNR';

    public static function listObjectType($value = null)
    {
        $list = [
            self::TYPE_NYILTHALMAZ => "nyílthalmaz",
            self::TYPE_GOMBHALMAZ => "gömbhalmaz",
            self::TYPE_DIFFUZ_KOD => "diffúz köd",
            self::TYPE_GALAXIS => "galaxis",
            self::TYPE_PLANETARIS_KOD => "planetáris köd",
            self::TYPE_CSILLAGKOD => "csillagköd",
            self::TYPE_SZUPERNOVA => "szupernova maradvány",
        ];

        if (empty($value)) {
            return $list;
        }

        return $list[$value] ?? null;
    }
}