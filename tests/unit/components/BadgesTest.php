<?php

namespace tests\unit\components;

use app\components\Badges;

/**
 * No database is needed.
 */
class BadgesTest extends \Codeception\Test\Unit
{
    public function testNothingIsDone()
    {
        $category = $this->build(['levels' => [1, 10]], 0);

        $this->assertSame([], Badges::getEarnedLevels($category));
        $this->assertSame(1, $category['next']['threshold']);
        $this->assertSame('Első észlelés', $category['next']['title']);
    }

    public function testLevelsAreEarnedAtTheirThreshold()
    {
        $this->assertCount(1, Badges::getEarnedLevels($this->build(['levels' => [1, 10]], 9)));

        $category = $this->build(['levels' => [1, 10]], 10);

        $this->assertCount(2, Badges::getEarnedLevels($category));
        $this->assertNull($category['next']);
    }

    public function testDateIsThatOfTheItemThatReachedTheThreshold()
    {
        $items = array_map(function ($date) {
            return ['date' => $date, 'isInPart' => false];
        }, ['2024-03-01', null, '2022-01-01']);
        $category = Badges::build(['key' => 'x', 'unit' => 'x', 'levels' => [1, 2, 3, 4]], $items);

        $this->assertSame(['', '2022-01-01', '2024-03-01', null], array_column($category['levels'], 'date'));
    }

    public function testPartIsSeparateFromTheWhole()
    {
        // 3 of the 5 items are in the part
        $definition = ['levels' => [2, 4], 'part' => 3, 'partTitle' => 'Rész', 'total' => 5, 'totalTitle' => 'Egész'];
        $category = $this->build($definition, 1, 2);

        // 4 is left out, it is not below the part
        $this->assertSame([2, 3, 5], array_column($category['levels'], 'threshold'));
        $this->assertSame([true, false, false], array_column($category['levels'], 'isEarned'));
        $this->assertSame(1, $category['next']['value']);

        $category = $this->build($definition, 3, 1);

        $this->assertSame([true, true, false], array_column($category['levels'], 'isEarned'));
        $this->assertSame(4, $category['next']['value']);
    }

    public function testPartThatIsTheWholeIsLeftOut()
    {
        $category = $this->build(['levels' => [2], 'part' => 5, 'partTitle' => 'Rész', 'total' => 5, 'totalTitle' => 'Egész'], 5);

        $this->assertSame([2, 5], array_column($category['levels'], 'threshold'));
        $this->assertNull($category['next']);
    }

    public function testColorsIncrease()
    {
        $tiers = array_column($this->build(['levels' => [1, 2, 3, 4, 5]], 0)['levels'], 'tier');
        $sorted = $tiers;
        sort($sorted);

        $this->assertSame($sorted, $tiers);
        $this->assertSame(1, reset($tiers));
        $this->assertSame(Badges::TIER_COUNT, end($tiers));
    }

    public function testEveryCategoryFitsTheColumn()
    {
        foreach (array_keys(Badges::getCategories()) as $key) {
            $this->assertLessThanOrEqual(16, strlen($key));
        }
    }

    private function build(array $definition, $inPart, $others = 0)
    {
        $items = array_merge(
            array_fill(0, $inPart, ['date' => '2024-01-01', 'isInPart' => true]),
            array_fill(0, $others, ['date' => '2024-01-01', 'isInPart' => false])
        );

        return Badges::build($definition + ['key' => 'x', 'unit' => 'észlelés', 'firstTitle' => 'Első észlelés'], $items);
    }
}
