<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityCondition;
<<<<<<< HEAD
use App\Models\FacilityScore;
=======
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityConditionTest extends TestCase
{
    use RefreshDatabase;

<<<<<<< HEAD
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
=======
    public function test_library_manager_can_create_update_and_delete_a_condition(): void
    {
        [$library, $park, $factory] = $this->createFacilities();
        $user = $this->libraryManager();

        $this->actingAs($user)
            ->post(route('facilities.conditions.store', $library), [
                'condition_type' => 'required_neighbour',
                'neighbour_facility_id' => $park->id,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $condition = FacilityCondition::firstOrFail();

        $this->assertDatabaseHas('facility_conditions', [
            'facility_id' => $library->id,
            'condition_type' => 'required_neighbour',
            'neighbour_facility_id' => $park->id,
        ]);

        $this->actingAs($user)
            ->patch(route('facilities.conditions.update', [$library, $condition]), [
                'condition_type' => 'forbidden_neighbour',
                'neighbour_facility_id' => $factory->id,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('facility_conditions', [
            'id' => $condition->id,
            'condition_type' => 'forbidden_neighbour',
            'neighbour_facility_id' => $factory->id,
        ]);

        $this->actingAs($user)
            ->delete(route('facilities.conditions.destroy', [$library, $condition]))
            ->assertRedirect();
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073

        $this->assertDatabaseMissing('facility_conditions', ['id' => $condition->id]);
    }

<<<<<<< HEAD
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
=======
    public function test_condition_cannot_target_the_same_facility_or_be_duplicated(): void
    {
        [$library, $park] = $this->createFacilities();
        $user = $this->libraryManager();

        $this->actingAs($user)
            ->post(route('facilities.conditions.store', $library), [
                'condition_type' => 'required_neighbour',
                'neighbour_facility_id' => $library->id,
            ])
            ->assertSessionHasErrors('neighbour_facility_id');

        FacilityCondition::create([
            'facility_id' => $library->id,
            'condition_type' => 'required_neighbour',
            'neighbour_facility_id' => $park->id,
        ]);

        $this->actingAs($user)
            ->post(route('facilities.conditions.store', $library), [
                'condition_type' => 'required_neighbour',
                'neighbour_facility_id' => $park->id,
            ])
            ->assertSessionHasErrors('neighbour_facility_id');
    }

    public function test_facilities_page_shows_condition_management_to_library_manager(): void
    {
        [$library, $park] = $this->createFacilities();
        $user = $this->libraryManager();

        FacilityCondition::create([
            'facility_id' => $library->id,
            'condition_type' => 'required_neighbour',
            'neighbour_facility_id' => $park->id,
        ]);

        $this->actingAs($user)
            ->get(route('facilities'))
            ->assertOk()
            ->assertSee('Function Conditions')
            ->assertSee('Requires neighbour')
            ->assertSee($park->name)
            ->assertSee(route('facilities.conditions.store', $library), false);
    }

    public function test_other_roles_cannot_manage_conditions(): void
    {
        [$library, $park] = $this->createFacilities();
        $cityPlanner = User::factory()->create(['role' => 'city_planner']);
        $policyMaker = $this->policyMaker();

        $this->actingAs($cityPlanner)
            ->post(route('facilities.conditions.store', $library), [
                'condition_type' => 'required_neighbour',
                'neighbour_facility_id' => $park->id,
            ])
            ->assertForbidden();

        $this->actingAs($cityPlanner)
            ->get(route('facilities'))
            ->assertOk()
            ->assertDontSee('Function Conditions');

        $this->actingAs($policyMaker)
            ->post(route('facilities.conditions.store', $library), [
                'condition_type' => 'required_neighbour',
                'neighbour_facility_id' => $park->id,
            ])
            ->assertForbidden();

        $this->actingAs($policyMaker)
            ->get(route('facilities'))
            ->assertOk()
            ->assertDontSee('Function Conditions');
    }

    public function test_library_manager_cannot_edit_facility_values(): void
    {
        [$library] = $this->createFacilities();
        $score = $library->scores()->create([
            'category_id' => $library->category_id,
            'score' => 3,
        ]);
        $libraryManager = $this->libraryManager();

        $this->actingAs($libraryManager)
            ->patchJson(route('facilities.scores.update', $score), ['score' => 5])
            ->assertForbidden();

        $this->actingAs($libraryManager)
            ->patch(route('facilities.update', $library), [
                'name' => 'Changed Library',
                'category_id' => $library->category_id,
                'icon' => 'changed',
                'scores' => [$library->category_id => 5],
            ])
            ->assertForbidden();

        $this->actingAs($libraryManager)
            ->get(route('facilities'))
            ->assertOk()
            ->assertDontSee('data-score-id=', false)
            ->assertSee('Function Conditions');

        $this->assertDatabaseHas('facilities', [
            'id' => $library->id,
            'name' => 'Library',
        ]);
        $this->assertDatabaseHas('facility_scores', [
            'id' => $score->id,
            'score' => 3,
        ]);
    }

    public function test_grid_receives_persisted_condition_data_and_feedback_area(): void
    {
        [$library, $park] = $this->createFacilities();

        FacilityCondition::create([
            'facility_id' => $library->id,
            'condition_type' => 'forbidden_neighbour',
            'neighbour_facility_id' => $park->id,
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('grid'))
            ->assertOk()
<<<<<<< HEAD
            ->assertViewHas('effectData', fn (array $data): bool => $data['neighbourRules'][$hospital->id]['requiredNeighbourId'] === $police->id
            )
            ->assertViewHas('restrictions', fn ($restrictions): bool => $restrictions->contains(fn (array $restriction): bool => $restriction['facility_id_1'] === min($club->id, $police->id)
                    && $restriction['facility_id_2'] === max($club->id, $police->id)
            )
            );
=======
            ->assertSee('id="condition-status"', false)
            ->assertSee('window.gridConditionData', false)
            ->assertViewHas('conditionData', function ($conditionData) use ($library, $park): bool {
                $condition = $conditionData[(string) $library->id]['conditions']->first();

                return $condition['type'] === 'forbidden_neighbour'
                    && $condition['neighbourFacilityId'] === (string) $park->id;
            });
    }

    /**
     * @return array<int, Facility>
     */
    private function createFacilities(): array
    {
        $category = Category::create([
            'name' => 'Public Services',
            'slug' => 'public-services',
            'sort_order' => 1,
        ]);

        return collect(['Library', 'Park', 'Factory'])
            ->map(fn (string $name, int $index) => Facility::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => strtolower($name),
                'icon' => strtoupper($name[0]),
                'sort_order' => $index + 1,
            ]))
            ->all();
    }

    private function policyMaker(): User
    {
        return User::factory()->create([
            'name' => 'Policy Maker',
            'email' => 'policy.maker@example.com',
            'password' => 'Password',
            'role' => 'policy_maker',
        ]);
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
    }

    private function libraryManager(): User
    {
<<<<<<< HEAD
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
=======
        return User::factory()->create([
            'name' => 'Library Manager',
            'email' => 'library.manager@example.com',
            'password' => 'Password',
            'role' => 'library_manager',
>>>>>>> 9b1fb50cbd837e928587d63b45472150bab40073
        ]);
    }
}
