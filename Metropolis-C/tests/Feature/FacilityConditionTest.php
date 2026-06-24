<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityCondition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityConditionTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_manager_can_manage_a_condition_through_central_routes(): void
    {
        [$library, $park, $factory] = $this->createFacilities();
        $manager = $this->libraryManager();

        $this->actingAs($manager)
            ->post(route('functions.conditions.store'), [
                'facility_id' => $library->id,
                'type' => FacilityCondition::REQUIRED_NEIGHBOUR,
                'related_facility_id' => $park->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $condition = FacilityCondition::firstOrFail();

        $this->actingAs($manager)
            ->patch(route('functions.conditions.update', $condition), [
                'facility_id' => $library->id,
                'type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
                'related_facility_id' => $factory->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('facility_conditions', [
            'id' => $condition->id,
            'facility_id' => min($library->id, $factory->id),
            'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
            'neighbour_facility_id' => max($library->id, $factory->id),
        ]);

        $this->actingAs($manager)
            ->delete(route('functions.conditions.destroy', $condition))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('facility_conditions', ['id' => $condition->id]);
    }

    public function test_library_manager_cannot_create_level_4_adjacency_condition(): void
    {
        [$library, $park] = $this->createFacilities();
        $manager = $this->libraryManager();

        $this->actingAs($manager)
            ->post(route('functions.conditions.store'), [
                'facility_id' => $library->id,
                'type' => FacilityCondition::LEVEL_4_ADJACENCY,
                'related_facility_id' => $park->id,
            ])
            ->assertSessionHasErrors('type');

        $this->assertDatabaseMissing('facility_conditions', [
            'facility_id' => min($library->id, $park->id),
            'condition_type' => FacilityCondition::LEVEL_4_ADJACENCY,
            'neighbour_facility_id' => max($library->id, $park->id),
        ]);
    }

    public function test_library_manager_can_manage_a_condition_through_facility_routes(): void
    {
        [$library, $park, $factory] = $this->createFacilities();
        $manager = $this->libraryManager();

        $this->actingAs($manager)
            ->post(route('functions.function.conditions.store', $library), [
                'condition_type' => FacilityCondition::REQUIRED_NEIGHBOUR,
                'neighbour_facility_id' => $park->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $condition = FacilityCondition::firstOrFail();

        $this->actingAs($manager)
            ->patch(route('functions.function.conditions.update', [$library, $condition]), [
                'condition_type' => FacilityCondition::FORBIDDEN_NEIGHBOUR,
                'neighbour_facility_id' => $factory->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($manager)
            ->delete(route('functions.function.conditions.destroy', [$library, $condition]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('facility_conditions', ['id' => $condition->id]);
    }

    public function test_condition_cannot_reference_itself_or_be_duplicated(): void
    {
        [$library, $park] = $this->createFacilities();
        $manager = $this->libraryManager();

        $this->actingAs($manager)
            ->post(route('functions.function.conditions.store', $library), [
                'condition_type' => FacilityCondition::REQUIRED_NEIGHBOUR,
                'neighbour_facility_id' => $library->id,
            ])
            ->assertSessionHasErrors('neighbour_facility_id');

        FacilityCondition::create([
            'facility_id' => $library->id,
            'condition_type' => FacilityCondition::REQUIRED_NEIGHBOUR,
            'neighbour_facility_id' => $park->id,
        ]);

        $this->actingAs($manager)
            ->post(route('functions.function.conditions.store', $library), [
                'condition_type' => FacilityCondition::REQUIRED_NEIGHBOUR,
                'neighbour_facility_id' => $park->id,
            ])
            ->assertSessionHasErrors('neighbour_facility_id');
    }

    public function test_other_roles_cannot_manage_conditions(): void
    {
        [$library, $park] = $this->createFacilities();

        foreach (['admin', 'city_planner', 'policy_maker'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->post(route('functions.conditions.store'), [
                    'facility_id' => $library->id,
                    'type' => FacilityCondition::REQUIRED_NEIGHBOUR,
                    'related_facility_id' => $park->id,
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseEmpty('facility_conditions');
    }

    public function test_grid_receives_condition_data(): void
    {
        [$library, $park] = $this->createFacilities();

        FacilityCondition::create([
            'facility_id' => $library->id,
            'condition_type' => FacilityCondition::REQUIRED_NEIGHBOUR,
            'neighbour_facility_id' => $park->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('grid'))
            ->assertOk()
            ->assertViewHas(
                'effectData',
                fn (array $data): bool => $data['neighbourRules'][$library->id]['requiredNeighbourId'] === $park->id
            )
            ->assertViewHas('conditionData');
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

    private function libraryManager(): User
    {
        return User::factory()->create(['role' => 'library_manager']);
    }
}
