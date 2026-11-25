<?php

namespace tests\unit\components;

use app\components\SkyMap;
use app\components\SkyMatcher;

/**
 * No database is needed.
 */
class SkyMapTest extends \Codeception\Test\Unit
{
    public function testNothingIsCovered()
    {
        list($coverage, $gaps) = SkyMap::findCoverage([]);

        $this->assertSame(0, $coverage);
        $this->assertCount(SkyMap::GAP_COUNT, $gaps);
    }

    public function testCoverageOfAnObject()
    {
        list($coverage) = SkyMap::findCoverage([['ra' => 0, 'dec' => 45]]);

        // area of the cap divided by the area of the sky above the horizon
        $expected = (1 - cos(deg2rad(SkyMap::COVER_RADIUS))) / (1 - sin(deg2rad(SkyMap::HORIZON_DEC)));

        $this->assertEqualsWithDelta($expected, $coverage, 0.0005);
    }

    public function testObjectBelowTheHorizonDoesNotCount()
    {
        list($coverage) = SkyMap::findCoverage([['ra' => 0, 'dec' => -70]]);

        $this->assertSame(0, $coverage);
    }

    public function testEverythingIsCovered()
    {
        $objects = [];

        for ($dec = -88; $dec <= 88; $dec += 2) {
            for ($ra = 0; $ra < 360; $ra += 2) {
                $objects[] = ['ra' => $ra, 'dec' => $dec];
            }
        }

        list($coverage, $gaps) = SkyMap::findCoverage($objects);

        $this->assertSame(1, $coverage);
        $this->assertSame([], $gaps);
    }

    public function testGapsAreEmptyAndApart()
    {
        $objects = [['ra' => 10, 'dec' => 40], ['ra' => 130, 'dec' => 20], ['ra' => 250, 'dec' => -10]];
        list(, $gaps) = SkyMap::findCoverage($objects);

        foreach ($gaps as $index => $gap) {
            $this->assertGreaterThanOrEqual(SkyMap::GAP_DEC, $gap['dec']);

            foreach ($objects as $object) {
                $this->assertGreaterThanOrEqual($gap['radius'] - 0.0001, SkyMatcher::distance($gap, $object));
            }

            foreach (array_slice($gaps, $index + 1) as $other) {
                $this->assertGreaterThanOrEqual(SkyMap::GAP_DISTANCE, SkyMatcher::distance($gap, $other));
                $this->assertGreaterThanOrEqual($other['radius'], $gap['radius']);
            }
        }
    }
}
