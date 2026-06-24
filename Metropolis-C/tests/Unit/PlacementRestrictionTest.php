<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BES.3.b - Restrict certain functions from being placed next to each other.
 * A restriction is stored as a forbidden_neighbour condition.
 */
class PlacementRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_forbidden_neighbour_restriction_is_stored_and_recognised(): void
    {
        [$policeStation, $nightClub] = $this->createFacilities('Police Station', 'Night Club');

        $restriction = FacilityCondition::create([
            'facility_id' => $policeStation->id,
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => $nightClub->id,
        ]);

        $this->assertDatabaseHas('facility_conditions', [
            'facility_id' => $policeStation->id,
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => $nightClub->id,
        ]);

        $this->assertTrue($restriction->isForbiddenNeighbour());
        $this->assertFalse($restriction->isRequiredNeighbour());
    }

    public function test_restricted_functions_are_detected_as_a_placement_conflict(): void
    {
        [$policeStation, $nightClub, $park] = $this->createFacilities('Police Station', 'Night Club', 'Park');

        FacilityCondition::create([
            'facility_id' => $policeStation->id,
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => $nightClub->id,
        ]);

        $restrictions = $this->restrictionPairs();

        $this->assertSame(
            [$policeStation->id],
            $this->restrictionConflicts($restrictions, $nightClub->id, [$policeStation->id, $park->id])
        );

        $this->assertSame(
            [],
            $this->restrictionConflicts($restrictions, $park->id, [$policeStation->id, $nightClub->id])
        );
    }

    /**
     * Forbidden-neighbour pairs in the same shape the grid receives them.
     *
     * @return array<int, array{facility_id_1: int, facility_id_2: int}>
     */
    private function restrictionPairs(): array
    {
        return FacilityCondition::query()
            ->where('condition_type', FacilityCondition::FORBIDDEN_NEIGHBOUR)
            ->get()
            ->map(fn (FacilityCondition $condition): array => [
                'facility_id_1' => $condition->facility_id,
                'facility_id_2' => $condition->neighbour_facility_id,
            ])
            ->all();
    }

    /**
     * PHP mirror of findRestrictionConflicts in effectGrid.js: returns the
     * adjacent facility ids that conflict with the dropped facility.
     *
     * @param  array<int, array{facility_id_1: int, facility_id_2: int}>  $restrictions
     * @param  array<int, int>  $adjacentIds
     * @return array<int, int>
     */
    private function restrictionConflicts(array $restrictions, int $facilityId, array $adjacentIds): array
    {
        return array_values(array_filter($adjacentIds, function (int $id) use ($restrictions, $facilityId): bool {
            foreach ($restrictions as $pair) {
                $matchesForward = $pair['facility_id_1'] === $facilityId && $pair['facility_id_2'] === $id;
                $matchesReverse = $pair['facility_id_2'] === $facilityId && $pair['facility_id_1'] === $id;

                if ($matchesForward || $matchesReverse) {
                    return true;
                }
            }

            return false;
        }));
    }

    /**
     * @return array<int, Facility>
     */
    private function createFacilities(string ...$names): array
    {
        $category = Category::create([
            'name' => 'Facilities',
            'slug' => 'facilities',
            'sort_order' => 1,
        ]);

        return collect($names)
            ->map(fn (string $name, int $index): Facility => Facility::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name),
                'icon' => strtoupper($name[0]),
                'sort_order' => $index + 1,
            ]))
            ->all();
    }
}
