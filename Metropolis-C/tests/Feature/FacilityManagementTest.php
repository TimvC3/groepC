<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Facility;
use App\Models\FacilityScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_facilities_management_page(): void
    {
        [$security, $mobility] = $this->createCategories();
        $facility = $this->createFacilityWithScores($security, [
            $security->id => 5,
            $mobility->id => -2,
        ]);

        $response = $this
            ->actingAs($this->adminUser())
            ->get(route('facilities'));

        $response
            ->assertOk()
            ->assertSee('Existing Facilities')
            ->assertSee('Create Facility')
            ->assertSee('Facility Score Matrix')
            ->assertSee($facility->name)
            ->assertSee(route('facilities.edit', $facility), false);
    }

    public function test_non_admin_cannot_create_or_edit_facilities(): void
    {
        [$security, $mobility] = $this->createCategories();
        $facility = $this->createFacilityWithScores($security, [
            $security->id => 5,
            $mobility->id => -2,
        ]);
        $user = User::factory()->create(['role' => 'city_planner']);

        $this
            ->actingAs($user)
            ->post(route('facilities.store'), [
                'name' => 'Hospital',
                'category_id' => $security->id,
                'icon' => 'hospital',
                'scores' => [$security->id => 3],
            ])
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->get(route('facilities.edit', $facility))
            ->assertForbidden();

        $this
            ->actingAs($user)
            ->patch(route('facilities.update', $facility), [
                'name' => 'Changed Name',
                'category_id' => $mobility->id,
                'icon' => 'changed',
                'scores' => [
                    $security->id => 1,
                    $mobility->id => 2,
                ],
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => 'Police Station',
            'category_id' => $security->id,
            'icon' => 'police',
        ]);
    }

    public function test_admin_can_create_facility_with_scores(): void
    {
        [$security, $mobility] = $this->createCategories();
        $response = $this
            ->actingAs($this->adminUser())
            ->post(route('facilities.store'), [
                'name' => 'Hospital',
                'category_id' => $security->id,
                'icon' => 'hospital',
                'scores' => [
                    $security->id => 4,
                    $mobility->id => -1,
                ],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('facilities'));

        $this->assertDatabaseHas('facilities', [
            'category_id' => $security->id,
            'name' => 'Hospital',
            'slug' => 'hospital',
            'icon' => 'hospital',
            'sort_order' => 1,
        ]);

        $facility = Facility::where('slug', 'hospital')->firstOrFail();

        $this->assertDatabaseHas('facility_scores', [
            'facility_id' => $facility->id,
            'category_id' => $security->id,
            'score' => 4,
        ]);
        $this->assertDatabaseHas('facility_scores', [
            'facility_id' => $facility->id,
            'category_id' => $mobility->id,
            'score' => -1,
        ]);
    }

    public function test_edit_page_prefills_selected_facility_data(): void
    {
        [$security, $mobility] = $this->createCategories();
        $facility = $this->createFacilityWithScores($security, [
            $security->id => 5,
            $mobility->id => -2,
        ]);

        $response = $this
            ->actingAs($this->adminUser())
            ->get(route('facilities.edit', $facility));

        $response
            ->assertOk()
            ->assertSee('Edit Facility')
            ->assertSee('value="Police Station"', false)
            ->assertSee('value="police"', false)
            ->assertSee('value="5"', false)
            ->assertSee('value="-2"', false)
            ->assertSee(route('facilities.update', $facility), false);
    }

    public function test_admin_can_update_facility_and_scores(): void
    {
        [$security, $mobility] = $this->createCategories();
        $facility = $this->createFacilityWithScores($security, [
            $security->id => 5,
            $mobility->id => -2,
        ]);
        $response = $this
            ->actingAs($this->adminUser())
            ->patch(route('facilities.update', $facility), [
                'name' => 'Emergency Center',
                'category_id' => $mobility->id,
                'icon' => 'emergency',
                'scores' => [
                    $security->id => 2,
                    $mobility->id => 4,
                ],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('facilities'));

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'category_id' => $mobility->id,
            'name' => 'Emergency Center',
            'slug' => 'emergency-center',
            'icon' => 'emergency',
        ]);

        $this->assertDatabaseHas('facility_scores', [
            'facility_id' => $facility->id,
            'category_id' => $security->id,
            'score' => 2,
        ]);
        $this->assertDatabaseHas('facility_scores', [
            'facility_id' => $facility->id,
            'category_id' => $mobility->id,
            'score' => 4,
        ]);
    }

    public function test_update_generates_unique_slug_when_name_already_exists(): void
    {
        [$security] = $this->createCategories();
        Facility::create([
            'category_id' => $security->id,
            'name' => 'Library',
            'slug' => 'library',
            'icon' => 'library',
            'sort_order' => 1,
        ]);
        $facility = $this->createFacilityWithScores($security, [
            $security->id => 5,
        ]);

        $response = $this
            ->actingAs($this->adminUser())
            ->patch(route('facilities.update', $facility), [
                'name' => 'Library',
                'category_id' => $security->id,
                'icon' => 'library-alt',
                'scores' => [$security->id => 1],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('facilities'));

        $this->assertDatabaseHas('facilities', [
            'id' => $facility->id,
            'name' => 'Library',
            'slug' => 'library-1',
            'icon' => 'library-alt',
        ]);
    }

    public function test_non_admin_cannot_update_score_from_score_matrix(): void
    {
        [$security, $mobility] = $this->createCategories();
        $facility = $this->createFacilityWithScores($security, [
            $security->id => 5,
            $mobility->id => -2,
        ]);
        $score = $facility->scores()->where('category_id', $mobility->id)->firstOrFail();

        $response = $this
            ->actingAs(User::factory()->create(['role' => 'city_planner']))
            ->patchJson(route('facilities.scores.update', $score), [
                'score' => 3,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('facility_scores', [
            'id' => $score->id,
            'score' => -2,
        ]);
    }

    public function test_facility_management_validates_scores(): void
    {
        [$security] = $this->createCategories();

        $response = $this
            ->actingAs($this->adminUser())
            ->post(route('facilities.store'), [
                'name' => 'Invalid Score Facility',
                'category_id' => $security->id,
                'icon' => 'invalid',
                'scores' => [$security->id => 9],
            ]);

        $response->assertSessionHasErrors('scores.'.$security->id);
    }

    private function adminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * @return array<int, Category>
     */
    private function createCategories(): array
    {
        return [
            Category::create([
                'name' => 'Security',
                'slug' => 'security',
                'sort_order' => 1,
            ]),
            Category::create([
                'name' => 'Mobility',
                'slug' => 'mobility',
                'sort_order' => 2,
            ]),
        ];
    }

    /**
     * @param  array<int, int>  $scoresByCategory
     */
    private function createFacilityWithScores(Category $category, array $scoresByCategory): Facility
    {
        $facility = Facility::create([
            'category_id' => $category->id,
            'name' => 'Police Station',
            'slug' => 'police-station',
            'icon' => 'police',
            'sort_order' => 1,
        ]);

        foreach ($scoresByCategory as $categoryId => $score) {
            FacilityScore::create([
                'facility_id' => $facility->id,
                'category_id' => $categoryId,
                'score' => $score,
            ]);
        }

        return $facility;
    }
}
