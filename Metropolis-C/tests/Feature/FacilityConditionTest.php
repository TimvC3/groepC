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

    public function test_authenticated_user_can_create_update_and_delete_a_condition(): void
    {
        [$library, $park, $factory] = $this->createFacilities();
        $user = User::factory()->create();

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

        $this->assertDatabaseMissing('facility_conditions', ['id' => $condition->id]);
    }

    public function test_condition_cannot_target_the_same_facility_or_be_duplicated(): void
    {
        [$library, $park] = $this->createFacilities();
        $user = User::factory()->create();

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

    public function test_facilities_page_shows_condition_management_to_non_admin_user(): void
    {
        [$library, $park] = $this->createFacilities();
        $user = User::factory()->create(['role' => 'city_planner']);

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

    public function test_grid_receives_persisted_condition_data_and_feedback_area(): void
    {
        [$library, $park] = $this->createFacilities();

        FacilityCondition::create([
            'facility_id' => $library->id,
            'condition_type' => 'forbidden_neighbour',
            'neighbour_facility_id' => $park->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('grid'))
            ->assertOk()
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
}
