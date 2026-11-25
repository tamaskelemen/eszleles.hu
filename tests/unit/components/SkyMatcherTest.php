<?php

namespace tests\unit\components;

use app\components\SkyMatcher;

/**
 * The titles are from real observations. No database is needed.
 */
class SkyMatcherTest extends \Codeception\Test\Unit
{
    /** name => constellation, ra, dec, other aliases */
    const OBJECTS = [
        'NGC 1' => ['Peg', 1.8, 27.7, []],
        'NGC 539' => ['Cet', 21.3, -18.2, []],
        'NGC 700' => ['And', 28.1, 36.1, []],
        'NGC 869' => ['Per', 34.7, 57.1, ['Ikerhalmaz']],
        'NGC 884' => ['Per', 35.6, 57.1, ['Ikerhalmaz']],
        'NGC 1499' => ['Per', 60.2, 36.4, ['Kalifornia-köd']],
        'NGC 1952' => ['Tau', 83.6, 22.0, ['M 1']],
        'NGC 1976' => ['Ori', 83.8, -5.4, ['M 42', 'Orion-köd']],
        'NGC 1982' => ['Ori', 83.9, -5.3, ['M 43']],
        'NGC 3031' => ['UMa', 148.9, 69.1, ['M 81']],
        'NGC 3034' => ['UMa', 149.0, 69.7, ['M 82']],
        'NGC 3185' => ['Leo', 154.4, 21.7, []],
        'NGC 3187' => ['Leo', 154.4, 21.9, []],
        'NGC 3193' => ['Leo', 154.6, 21.9, []],
        'NGC 4486' => ['Vir', 187.7, 12.4, ['M 87']],
        'NGC 4486B' => ['Vir', 187.6, 12.5, []],
        'NGC 5272' => ['CVn', 205.5, 28.4, ['M 3']],
        'NGC 5457' => ['UMa', 210.8, 54.3, ['M 101', 'Szélkerék-galaxis']],
        'NGC 6205' => ['Her', 250.4, 36.5, ['M 13', 'Herkules-gömbhalmaz']],
        'NGC 7000' => ['Cyg', 314.7, 44.5, ['Észak-Amerika-köd']],
        'IC 1805' => ['Cas', 38.2, 61.5, []],
        'Mel 22' => ['Tau', 56.9, 24.1, ['M 45', 'Fiastyúk']],
        'C 9' => ['Cep', 344.5, 62.5, ['Sh 2-155']],
        'B 33' => ['Ori', 85.2, -2.5, ['Lófej-köd']],
        'B 44' => ['Oph', 250.1, -24.1, []],
        'B 44A' => ['Oph', 251.2, -23.9, []],
        'LDN 1355' => ['Cas', 43.3, 68.7, []],
        'LDN 1357' => ['Cas', 44.1, 68.3, []],
        'LDN 1358' => ['Cas', 44.2, 68.6, []],
        'vdB 152' => ['Cep', 333.4, 70.3, []],
    ];

    public function testMatchTags()
    {
        $auto = SkyMatcher::STATUS_AUTO;
        $review = SkyMatcher::STATUS_REVIEW;

        foreach ([
            [['M 42'], ['NGC 1976' => $auto]],
            [['ngc1976', 'M 42'], ['NGC 1976' => $auto]],
            [['M 81 / M 82'], ['NGC 3031' => $auto, 'NGC 3034' => $auto]],
            [['Orion-köd (M 42)'], ['NGC 1976' => $auto]],
            [['Orion-köd'], ['NGC 1976' => $review]],
            [['HOLD', 'teszt', '', 'M13 környéke', 'NGC 99999'], []],
            [['M 43'], ['NGC 1982' => $auto], ['NGC 1976']],
            [['M 13'], ['NGC 6205' => $review], ['NGC 1976']],
        ] as $index => $case) {
            $known = array_map(function ($name) {
                return ['ra' => self::OBJECTS[$name][1], 'dec' => self::OBJECTS[$name][2]];
            }, $case[2] ?? []);
            $statuses = [];

            foreach ($this->createMatcher()->matchTags($case[0], $known) as $match) {
                $statuses[$match['object']['name']] = $match['status'];
            }

            $this->assertSame($case[1], $statuses, "case $index");
        }
    }

    public function testMatch()
    {
        // not a data provider, because those run before the autoloader of Yii is set up
        foreach ($this->titles() as $case => $data) {
            list($title, $names) = $data;
            $match = $this->match($title, $data[3] ?? null);

            $this->assertSame($names, array_column($match['objects'], 'name'), $case);
            $this->assertSame($data[2] ?? SkyMatcher::STATUS_AUTO, $match['status'], $case);
        }
    }

    private function titles()
    {
        $review = SkyMatcher::STATUS_REVIEW;
        $none = SkyMatcher::STATUS_NONE;

        return [
            'messier' => ['M13', ['NGC 6205']],
            'messier, with words' => ['Messier 13 gömbhalmaz', ['NGC 6205']],
            'lower case' => ['Ngc-1982 & m 42', ['NGC 1982', 'NGC 1976']],
            'leading zeros' => ['NGC0884', ['NGC 884']],
            'in two catalogs' => ['Trapézium (M42, NGC 1976)', ['NGC 1976']],
            'type without space' => ['M42DF', ['NGC 1976']],
            'suffix of the uploader' => ['IC1805C  (Szív köd)', ['IC 1805']],
            'suffix of the catalog' => ['NGC 4486B', ['NGC 4486B']],
            'sharpless' => ['Barlang köd (SH2-155)', ['C 9']],
            'sharpless, spaces' => ['Sh 2 - 155', ['C 9']],
            'sharpless, long' => ['Sharpless 155', ['C 9']],
            'barnard' => ['Barnard 33', ['B 33']],
            'supernova' => ['Messier 101 - SN 2023ixf', ['NGC 5457']],

            'two messiers' => ['M81-M82, félsiker', ['NGC 3031', 'NGC 3034']],
            'list with dash' => ['Orion köd (M42–43) és a Futóember', ['NGC 1976', 'NGC 1982']],
            'list with plus' => ['NGC 869+884', ['NGC 869', 'NGC 884']],
            'list of dark nebulae' => ['LDN 1357, 1355, 1358 - Cassiopeia', ['LDN 1357', 'LDN 1355', 'LDN 1358']],
            'barnard with a letter' => ['B 44a és B44', ['B 44A', 'B 44']],
            'van den bergh' => ['VdB152', ['vdB 152']],
            'list with type' => ['HCG 44, NGC 3185, 3187, 3193 GX (Leo)', ['NGC 3185', 'NGC 3187', 'NGC 3193']],
            'list with a word after it' => ['M81-82 galaxisok', ['NGC 3031', 'NGC 3034']],
            'exposure is not a list' => ['M42, 43 perc', ['NGC 1976']],
            'far objects' => ['LDN 1355 és M13', ['LDN 1355', 'NGC 6205'], $review],
            'far number is not a list' => ['NGC 7000, 1', ['NGC 7000']],
            'date is not a list' => ['M42 - 2026.01.12', ['NGC 1976']],

            'nickname' => ['A Kalifornia köd és a Fiastyúk', ['NGC 1499', 'Mel 22'], $review],
            'nickname of two objects' => ['Perseus ikerhalmaz', ['NGC 869', 'NGC 884'], $review],
            'nickname, one word' => ['Lófejköd', ['B 33'], $review],
            'nickname with suffix' => ['Vendégcsillag a Szélkerék-galaxisban', ['NGC 5457'], $review],
            'nickname of a known number' => ['NGC7000 Észak-Amerika-köd', ['NGC 7000']],
            'nickname of something else' => ['Észak-Amerika-köd NGC700 (részlet)', ['NGC 700'], $review],

            'good constellation' => ['M3', ['NGC 5272'], SkyMatcher::STATUS_AUTO, 'CVn'],
            'bad constellation' => ['NGC 539 (559)', ['NGC 539'], $review, 'Cas'],
            'bad constellation in title' => ['M13 GH (Per)', ['NGC 6205'], $review],
            'unknown constellation' => ['M3', ['NGC 5272'], SkyMatcher::STATUS_AUTO, 'CVm'],

            'minkowski' => ['M1-92 PL (Cyg)', [], $none],
            'comet' => ['C/2022 E3 (ZTF) távcsővégen', [], $none],
            'comet with space' => ['C 2022 E3', [], $none],
            'no object' => ['Boldog H-alfás Új Évet', [], $none],
            'empty' => ['', [], $none],
        ];
    }

    public function testUnknownDesignation()
    {
        $this->assertSame(['LBN 331', 'SH2 101'], $this->match('LBN 331 és Sh2-101, Tulipán-köd')['unknown']);
        $this->assertSame([], $this->match('M 42')['unknown']);
    }

    public function testReasons()
    {
        $this->assertSame([SkyMatcher::REASON_NICKNAME], $this->match('Fiastyúk')['reasons']);
        $this->assertSame([SkyMatcher::REASON_CONSTELLATION], $this->match('M3', 'Boo')['reasons']);
        $this->assertSame([SkyMatcher::REASON_NICKNAME_CONFLICT], $this->match('Észak-Amerika-köd NGC700')['reasons']);
    }

    public function testFirstDesignationIsKept()
    {
        $matcher = new SkyMatcher(function () {
            $object = ['id' => 1, 'name' => 'NGC 2282', 'constellation' => 'Mon', 'ra' => 101.7, 'dec' => 1.3];

            return [
                'NGC 2282' => [$object + ['designation' => null]],
                'IC 2172' => [$object + ['designation' => 'IC 2172']],
            ];
        });

        $this->assertNull($matcher->match('NGC 2282 (IC 2172)')['objects'][0]['designation']);
        $this->assertSame('IC 2172', $matcher->match('IC 2172')['objects'][0]['designation']);
    }

    public function testDesignationKey()
    {
        $this->assertSame('NGC 224', SkyMatcher::designationKey('NGC 0224'));
        $this->assertSame('SH2 155', SkyMatcher::designationKey('Sh 2-155'));
        $this->assertNull(SkyMatcher::designationKey('Fátyol-köd'));
        $this->assertNull(SkyMatcher::designationKey('Melotte 15 és társai'));
    }

    public function testNicknameKey()
    {
        $this->assertSame('bagolyhalmaz', SkyMatcher::nicknameKey('Bagoly-halmaz'));
        $this->assertSame('gyuruskod', SkyMatcher::nicknameKey('Gyűrűs-köd'));
        $this->assertSame('csapgalaxis', SkyMatcher::nicknameKey('CSÁP galaxisok'));
    }

    public function testDistance()
    {
        $this->assertEqualsWithDelta(90, SkyMatcher::distance(['ra' => 0, 'dec' => 0], ['ra' => 90, 'dec' => 0]), 0.0001);
        $this->assertEqualsWithDelta(2, SkyMatcher::distance(['ra' => 359, 'dec' => 0], ['ra' => 1, 'dec' => 0]), 0.0001);
    }

    private function match($title, $constellation = null)
    {
        return $this->createMatcher()->match($title, $constellation);
    }

    private function createMatcher()
    {
        $aliases = [];

        foreach (self::OBJECTS as $name => list($objectConstellation, $ra, $dec, $others)) {
            foreach (array_merge([$name], $others) as $alias) {
                $key = SkyMatcher::designationKey($alias) ?? SkyMatcher::nicknameKey($alias);
                $aliases[$key][] = ['id' => $name, 'name' => $name, 'constellation' => $objectConstellation, 'ra' => $ra, 'dec' => $dec];
            }
        }

        return new SkyMatcher(function ($keys) use ($aliases) {
            return array_intersect_key($aliases, array_flip($keys));
        });
    }
}
