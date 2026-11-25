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

    /**
     * @param string|null $value IAU abbreviation
     * @return array|string|null
     */
    public static function listConstellation($value = null)
    {
        $list = [
            'And' => 'Androméda',
            'Ant' => 'Légszivattyú',
            'Aps' => 'Paradicsommadár',
            'Aqr' => 'Vízöntő',
            'Aql' => 'Sas',
            'Ara' => 'Oltár',
            'Ari' => 'Kos',
            'Aur' => 'Szekeres',
            'Boo' => 'Ökörhajcsár',
            'Cae' => 'Véső',
            'Cam' => 'Zsiráf',
            'Cnc' => 'Rák',
            'CVn' => 'Vadászebek',
            'CMa' => 'Nagy Kutya',
            'CMi' => 'Kis Kutya',
            'Cap' => 'Bak',
            'Car' => 'Hajógerinc',
            'Cas' => 'Cassiopeia',
            'Cen' => 'Kentaur',
            'Cep' => 'Cefeusz',
            'Cet' => 'Cet',
            'Cha' => 'Kaméleon',
            'Cir' => 'Körző',
            'Col' => 'Galamb',
            'Com' => 'Bereniké Haja',
            'CrA' => 'Déli Korona',
            'CrB' => 'Északi Korona',
            'Crv' => 'Holló',
            'Crt' => 'Serleg',
            'Cru' => 'Dél Keresztje',
            'Cyg' => 'Hattyú',
            'Del' => 'Delfin',
            'Dor' => 'Aranyhal',
            'Dra' => 'Sárkány',
            'Equ' => 'Csikó',
            'Eri' => 'Eridánusz',
            'For' => 'Kemence',
            'Gem' => 'Ikrek',
            'Gru' => 'Daru',
            'Her' => 'Herkules',
            'Hor' => 'Ingaóra',
            'Hya' => 'Északi Vízikígyó',
            'Hyi' => 'Déli Vízikígyó',
            'Ind' => 'Indián',
            'Lac' => 'Gyík',
            'Leo' => 'Oroszlán',
            'LMi' => 'Kis Oroszlán',
            'Lep' => 'Nyúl',
            'Lib' => 'Mérleg',
            'Lup' => 'Farkas',
            'Lyn' => 'Hiúz',
            'Lyr' => 'Lant',
            'Men' => 'Táblahegy',
            'Mic' => 'Mikroszkóp',
            'Mon' => 'Egyszarvú',
            'Mus' => 'Légy',
            'Nor' => 'Szögmérő',
            'Oct' => 'Oktáns',
            'Oph' => 'Kígyótartó',
            'Ori' => 'Orion',
            'Pav' => 'Páva',
            'Peg' => 'Pegazus',
            'Per' => 'Perzeusz',
            'Phe' => 'Főnix',
            'Pic' => 'Festő',
            'Psc' => 'Halak',
            'PsA' => 'Déli Hal',
            'Pup' => 'Hajófar',
            'Pyx' => 'Tájoló',
            'Ret' => 'Háló',
            'Sge' => 'Nyíl',
            'Sgr' => 'Nyilas',
            'Sco' => 'Skorpió',
            'Scl' => 'Szobrász',
            'Sct' => 'Pajzs',
            'Ser' => 'Kígyó',
            'Sex' => 'Szextáns',
            'Tau' => 'Bika',
            'Tel' => 'Távcső',
            'Tri' => 'Háromszög',
            'TrA' => 'Déli Háromszög',
            'Tuc' => 'Tukán',
            'UMa' => 'Nagy Medve',
            'UMi' => 'Kis Medve',
            'Vel' => 'Vitorla',
            'Vir' => 'Szűz',
            'Vol' => 'Repülőhal',
            'Vul' => 'Kis Róka',
        ];

        if (empty($value)) {
            return $list;
        }

        return $list[$value] ?? null;
    }
}
