<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityCondition;
use App\Models\FacilityScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_manager_can_view_condition_management_without_facility_editing(): void
    {
        $facility = $this->createFacility('Police Station');

        $this->actingAs($this->libraryManager())
            ->get(route('facilities'))
            ->assertOk()
            ->assertSee('Function Conditions')
            ->assertSee('Required neighbour')
            ->assertSee('Forbidden neighbour')
            ->assertSee('Facility values are read-only for your role.')
            ->assertDontSee('Create Facility')
            ->assertDontSee(route('facilities.edit', $facility), false);
    }

    public function test_only_library_manager_can_manage_conditions(): void
    {
        $facility = $this->createFacility('Police Station');
        $neighbour = $this->createFacility('Hospital');

        foreach (['admin', 'city_planner', 'policy_maker'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->post(route('facility-conditions.store'), [
                    'facility_id' => $facility->id,
                    'type' => FacilityCondition::REQUIRED_NEIGHBOUR,
                    'related_facility_id' => $neighbour->id,
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseEmpty('facility_conditions');
    }

    public function test_library_manager_can_create_required_and_forbidden_neighbour_conditions(): void
    {
        $police = $this->createFacility('Police Station');
        $hospital = $this->createFacility('Hospital');
        $club = $this->createFacility('Night Club');

        $this->actingAs($this->libraryManager())
            ->post(route('facility-conditions.store'), [
                'facility_id' => $hospital->id,
                'type' => FacilityCondition::REQUIRED_NEIGHBOUR,
                'related_facility_id' => $police->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($this->libraryManager())
            ->post(route('facility-conditions.store'), [
                'facility_id' => $club->id,
                'type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
                'related_facility_id' => $police->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('facility_conditions', [
            'facility_id' => $hospital->id,
            'condition_type' => FacilityCondition::REQUIRED_NEIGHBOUR,
            'neighbour_facility_id' => $police->id,
        ]);
        $this->assertDatabaseHas('facility_conditions', [
            'facility_id' => min($club->id, $police->id),
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => max($club->id, $police->id),
        ]);
    }

    public function test_library_manager_can_edit_and_delete_a_condition(): void
    {
        $police = $this->createFacility('Police Station');
        $hospital = $this->createFacility('Hospital');
        $club = $this->createFacility('Night Club');
        $condition = FacilityCondition::create([
            'facility_id' => $hospital->id,
            'condition_type' => FacilityCondition::REQUIRED_NEIGHBOUR,
            'neighbour_facility_id' => $police->id,
        ]);

        $this->actingAs($this->libraryManager())
            ->patch(route('facility-conditions.update', $condition), [
                'facility_id' => $hospital->id,
                'type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
                'related_facility_id' => $club->id,
            ])
            ->assertRedirect(route('facilities'))
            ->assertSessionHas('success');

        $condition->refresh();
        $this->assertSame(FacilityCondition::FORBIDDEN_NEIGHBOUR, $condition->condition_type);

        $this->actingAs($this->libraryManager())
            ->delete(route('facility-conditions.destroy', $condition))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('facility_conditions', ['id' => $condition->id]);
    }

    public function test_condition_cannot_reference_the_same_function(): void
    {
        $facility = $this->createFacility('Police Station');

        $this->actingAs($this->libraryManager())
            ->post(route('facility-conditions.store'), [
                'facility_id' => $facility->id,
                'type' => FacilityCondition::REQUIRED_NEIGHBOUR,
                'related_facility_id' => $facility->id,
            ])
            ->assertSessionHasErrors('related_facility_id');
    }

    public function test_library_manager_cannot_edit_facility_values_or_scores(): void
    {
        $facility = $this->createFacility('Police Station');
        $score = FacilityScore::create([
            'facility_id' => $facility->id,
            'category_id' => $facility->category_id,
            'score' => 2,
        ]);
        $manager = $this->libraryManager();

        $this->actingAs($manager)
            ->patch(route('facilities.update', $facility), [
                'name' => 'Changed',
                'category_id' => $facility->category_id,
                'scores' => [$facility->category_id => 5],
            ])
            ->assertForbidden();

        $this->actingAs($manager)
            ->patchJson(route('facilities.scores.update', $score), ['score' => 5])
            ->assertForbidden();

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => 'Police Station',
        ]);
        $this->assertDatabaseHas('facility_scores', [
            'id' => $score->id,
            'score' => 2,
        ]);
    }

    public function test_grid_receives_central_conditions_for_immediate_application(): void
    {
        $police = $this->createFacility('Police Station');
        $hospital = $this->createFacility('Hospital');
        $club = $this->createFacility('Night Club');

        FacilityCondition::create([
            'facility_id' => $hospital->id,
            'condition_type' => FacilityCondition::REQUIRED_NEIGHBOUR,
            'neighbour_facility_id' => $police->id,
        ]);
        FacilityCondition::create([
            'facility_id' => min($club->id, $police->id),
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => max($club->id, $police->id),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('grid'))
            ->assertOk()
            ->assertViewHas('effectData', fn (array $data): bool => $data['neighbourRules'][$hospital->id]['requiredNeighbourId'] === $police->id
            )
            ->assertViewHas('restrictions', fn ($restrictions): bool => $restrictions->contains(fn (array $restriction): bool => $restriction['facility_id_1'] === min($club->id, $police->id)
                    && $restriction['facility_id_2'] === max($club->id, $police->id)
            )
            );
    }

    private function libraryManager(): User
    {
        return User::factory()->create(['role' => 'library_manager']);
    }

    private function createFacility(string $name): Facility
    {
        $category = Category::firstOrCreate(
            ['slug' => 'general'],
            ['name' => 'General', 'sort_order' => 1]
        );

        return Facility::create([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => str($name)->slug(),
            'icon' => 'building',
            'sort_order' => (Facility::max('sort_order') ?? 0) + 1,
        ]);
    }
}
