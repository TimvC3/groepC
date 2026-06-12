<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityRestriction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_restriction_section_on_facilities_page(): void
    {
        $this->createFacility('Police Station');
        $this->createFacility('Night Club');

        $this->actingAs($this->adminUser())
            ->get(route('facilities'))
            ->assertOk()
            ->assertSee('Placement Restrictions')
            ->assertSee('No restrictions configured yet.');
    }

    public function test_non_admin_cannot_see_restriction_section(): void
    {
        $this->actingAs($this->cityPlannerUser())
            ->get(route('facilities'))
            ->assertOk()
            ->assertDontSee('Placement Restrictions');
    }

    public function test_admin_can_add_a_restriction(): void
    {
        $police = $this->createFacility('Police Station');
        $club = $this->createFacility('Night Club');

        $this->actingAs($this->adminUser())
            ->post(route('facilities.restrictions.store'), [
                'facility_id_1' => $police->id,
                'facility_id_2' => $club->id,
            ])
            ->assertRedirect(route('facilities'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('facility_restrictions', [
            'facility_id_1' => min($police->id, $club->id),
            'facility_id_2' => max($police->id, $club->id),
        ]);
    }

    public function test_restriction_is_stored_with_normalized_id_order(): void
    {
        $police = $this->createFacility('Police Station');
        $club = $this->createFacility('Night Club');

        // Submit in reverse order (higher id first)
        $this->actingAs($this->adminUser())
            ->post(route('facilities.restrictions.store'), [
                'facility_id_1' => max($police->id, $club->id),
                'facility_id_2' => min($police->id, $club->id),
            ]);

        $this->assertDatabaseHas('facility_restrictions', [
            'facility_id_1' => min($police->id, $club->id),
            'facility_id_2' => max($police->id, $club->id),
        ]);
    }

    public function test_admin_cannot_add_duplicate_restriction(): void
    {
        $police = $this->createFacility('Police Station');
        $club = $this->createFacility('Night Club');

        FacilityRestriction::create([
            'facility_id_1' => min($police->id, $club->id),
            'facility_id_2' => max($police->id, $club->id),
        ]);

        $this->actingAs($this->adminUser())
            ->post(route('facilities.restrictions.store'), [
                'facility_id_1' => $police->id,
                'facility_id_2' => $club->id,
            ])
            ->assertRedirect(route('facilities'))
            ->assertSessionHas('error');

        $this->assertSame(1, FacilityRestriction::count());
    }

    public function test_admin_cannot_restrict_a_facility_with_itself(): void
    {
        $police = $this->createFacility('Police Station');

        $this->actingAs($this->adminUser())
            ->post(route('facilities.restrictions.store'), [
                'facility_id_1' => $police->id,
                'facility_id_2' => $police->id,
            ])
            ->assertSessionHasErrors('facility_id_2');

        $this->assertDatabaseEmpty('facility_restrictions');
    }

    public function test_admin_can_remove_a_restriction(): void
    {
        $police = $this->createFacility('Police Station');
        $club = $this->createFacility('Night Club');

        $restriction = FacilityRestriction::create([
            'facility_id_1' => min($police->id, $club->id),
            'facility_id_2' => max($police->id, $club->id),
        ]);

        $this->actingAs($this->adminUser())
            ->delete(route('facilities.restrictions.destroy', $restriction))
            ->assertRedirect(route('facilities'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('facility_restrictions', ['id' => $restriction->id]);
    }

    public function test_non_admin_cannot_add_restriction(): void
    {
        $police = $this->createFacility('Police Station');
        $club = $this->createFacility('Night Club');

        $this->actingAs($this->cityPlannerUser())
            ->post(route('facilities.restrictions.store'), [
                'facility_id_1' => $police->id,
                'facility_id_2' => $club->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseEmpty('facility_restrictions');
    }

    public function test_non_admin_cannot_remove_restriction(): void
    {
        $police = $this->createFacility('Police Station');
        $club = $this->createFacility('Night Club');

        $restriction = FacilityRestriction::create([
            'facility_id_1' => min($police->id, $club->id),
            'facility_id_2' => max($police->id, $club->id),
        ]);

        $this->actingAs($this->cityPlannerUser())
            ->delete(route('facilities.restrictions.destroy', $restriction))
            ->assertForbidden();

        $this->assertDatabaseHas('facility_restrictions', ['id' => $restriction->id]);
    }

    public function test_restriction_requires_valid_facility_ids(): void
    {
        $this->actingAs($this->adminUser())
            ->post(route('facilities.restrictions.store'), [
                'facility_id_1' => 9999,
                'facility_id_2' => 9998,
            ])
            ->assertSessionHasErrors(['facility_id_1', 'facility_id_2']);
    }

    public function test_existing_restrictions_appear_on_facilities_page(): void
    {
        $police = $this->createFacility('Police Station');
        $club = $this->createFacility('Night Club');

        FacilityRestriction::create([
            'facility_id_1' => min($police->id, $club->id),
            'facility_id_2' => max($police->id, $club->id),
        ]);

        $this->actingAs($this->adminUser())
            ->get(route('facilities'))
            ->assertOk()
            ->assertSee('Police Station')
            ->assertSee('Night Club');
    }

    public function test_restriction_is_deleted_when_facility_is_deleted(): void
    {
        $police = $this->createFacility('Police Station');
        $club = $this->createFacility('Night Club');

        FacilityRestriction::create([
            'facility_id_1' => min($police->id, $club->id),
            'facility_id_2' => max($police->id, $club->id),
        ]);

        $police->delete();

        $this->assertDatabaseEmpty('facility_restrictions');
    }

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function cityPlannerUser(): User
    {
        return User::factory()->create(['role' => 'city_planner']);
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
            'slug' => \Illuminate\Support\Str::slug($name),
            'icon' => '🏢',
            'sort_order' => Facility::max('sort_order') + 1,
        ]);
    }
}
