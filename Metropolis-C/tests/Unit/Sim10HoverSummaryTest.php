<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class Sim10HoverSummaryTest extends TestCase
{
    public function test_surrounding_areas_are_detected_correctly(): void
    {
        $gridColumns = 4;
        $totalCells = 12;
        $index = 6;

        $surrounding = [];

        $row = floor(($index - 1) / $gridColumns);
        $column = ($index - 1) % $gridColumns;

        for ($i = 1; $i <= $totalCells; $i++) {
            $otherRow = floor(($i - 1) / $gridColumns);
            $otherColumn = ($i - 1) % $gridColumns;

            $isSurrounding =
                abs($otherRow - $row) <= 1 &&
                abs($otherColumn - $column) <= 1;

            if ($isSurrounding) {
                $surrounding[] = $i;
            }
        }

        $this->assertEquals(
            [1, 2, 3, 5, 6, 7, 9, 10, 11],
            $surrounding
        );
    }
}