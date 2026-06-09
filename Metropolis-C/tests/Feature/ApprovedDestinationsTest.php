<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovedDestinationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_destinations_are_visible_on_the_grid(): void
    {
        $user = User::factory()->create([
            'role' => 'city_planner',
        ]);

        $category = Category::create([
            'name' => 'Facilities',
            'slug' => 'facilities',
            'sort_order' => 1,
        ]);

        Facility::create([
            'category_id' => $category->id,
            'name' => 'Police Station',
            'slug' => 'police-station',
            'icon' => '🚓',
            'sort_order' => 1,
        ]);

        Facility::create([
            'category_id' => $category->id,
            'name' => 'Park',
            'slug' => 'park',
            'icon' => '🌳',
            'sort_order' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('grid'));

        $response->assertOk();

        $response->assertSee('data-approved="true"', false);
        $response->assertSee('border-green-500', false);
        $response->assertSee('Approved', false);
        $response->assertSee('data-item-type="facility"', false);
    }

    public function test_only_approved_cells_get_the_approved_marker(): void
    {
        $user = User::factory()->create([
            'role' => 'city_planner',
        ]);

        $category = Category::create([
            'name' => 'Facilities',
            'slug' => 'facilities',
            'sort_order' => 1,
        ]);

        Facility::create([
            'category_id' => $category->id,
            'name' => 'Police Station',
            'slug' => 'police-station',
            'icon' => '🚓',
            'sort_order' => 1,
        ]);

        Facility::create([
            'category_id' => $category->id,
            'name' => 'Park',
            'slug' => 'park',
            'icon' => '🌳',
            'sort_order' => 2,
        ]);

        Facility::create([
            'category_id' => $category->id,
            'name' => 'School',
            'slug' => 'school',
            'icon' => '🏫',
            'sort_order' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('grid'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertSame(2, substr_count($html, 'data-approved="true"'));
        $this->assertStringContainsString('data-index="2"', $html);
        $this->assertStringContainsString('data-index="6"', $html);
        $this->assertStringContainsString('border-green-500', $html);
        
        
    }
}