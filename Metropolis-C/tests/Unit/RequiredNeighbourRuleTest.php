<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Facility;
use App\Support\GridEffectData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequiredNeighbourRuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_grid_effect_data_contains_required_neighbour_rule_for_facility(): void
    {
        $category = Category::create([
            'name' => 'Facilities',
            'slug' => 'facilities',
            'sort_order' => 1,
        ]);

        $park = Facility::create([
            'category_id' => $category->id,
            'name' => 'Park',
            'slug' => 'park',
            'icon' => '🌳',
            'sort_order' => 1,
        ]);

        $school = Facility::create([
            'category_id' => $category->id,
            'name' => 'School',
            'slug' => 'school',
            'icon' => '🏫',
            'sort_order' => 2,
            'required_neighbour_facility_id' => $park->id,
        ]);

        $hospital = Facility::create([
            'category_id' => $category->id,
            'name' => 'Hospital',
            'slug' => 'hospital',
            'icon' => '🏥',
            'sort_order' => 3,
        ]);

        $effectData = GridEffectData::from(
            Category::orderBy('sort_order')->get(),
            Facility::with(['scores', 'requiredNeighbour'])
                ->orderBy('sort_order')
                ->get(),
        );

        $this->assertArrayHasKey('neighbourRules', $effectData);

        $this->assertSame(
            $park->id,
            $effectData['neighbourRules'][$school->id]['requiredNeighbourId']
        );

        $this->assertSame(
            'Park',
            $effectData['neighbourRules'][$school->id]['requiredNeighbourName']
        );

        $this->assertArrayNotHasKey($park->id, $effectData['neighbourRules']);
        $this->assertArrayNotHasKey($hospital->id, $effectData['neighbourRules']);
    }

    public function test_only_horizontal_and_vertical_cells_are_seen_as_neighbours(): void
    {
        $this->assertSame([2, 5], $this->directNeighboursForCell(1));

        $this->assertSame([2, 5, 7, 10], $this->directNeighboursForCell(6));

        $this->assertNotContains(1, $this->directNeighboursForCell(6));
        $this->assertNotContains(3, $this->directNeighboursForCell(6));
        $this->assertNotContains(9, $this->directNeighboursForCell(6));
        $this->assertNotContains(11, $this->directNeighboursForCell(6));
    }

    /**
     * Same grid logic as BES.3.a:
     * only direct horizontal and vertical neighbours count.
     */
    private function directNeighboursForCell(
        int $index,
        int $gridColumns = 4,
        int $totalCells = 12
    ): array {
        $row = intdiv($index - 1, $gridColumns);
        $column = ($index - 1) % $gridColumns;

        $neighbours = [];

        for ($otherIndex = 1; $otherIndex <= $totalCells; $otherIndex++) {
            $otherRow = intdiv($otherIndex - 1, $gridColumns);
            $otherColumn = ($otherIndex - 1) % $gridColumns;

            $isDirectNeighbour =
                abs($otherRow - $row) + abs($otherColumn - $column) === 1;

            if ($isDirectNeighbour) {
                $neighbours[] = $otherIndex;
            }
        }

        sort($neighbours);

        return $neighbours;
    }
}