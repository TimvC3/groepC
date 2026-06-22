<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilitySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_accessibility_settings_menu_is_available(): void
    {
        $user = User::factory()->create([
            'role' => 'city_planner',
        ]);

        $response = $this->actingAs($user)->get('/grid');

        $response->assertOk();

        $response->assertSee('Accessibility');
        $response->assertSee('Accessibility Settings');
    }

    public function test_colorblind_friendly_mode_is_included(): void
    {
        $user = User::factory()->create([
            'role' => 'city_planner',
        ]);

        $response = $this->actingAs($user)->get('/grid');

        $response->assertOk();

        $response->assertSee('Colorblind-friendly mode');
        $response->assertSee('colorblindMode', false);
    }

    public function test_unneeded_visual_modes_are_not_shown(): void
    {
        $user = User::factory()->create([
            'role' => 'city_planner',
        ]);

        $response = $this->actingAs($user)->get('/grid');

        $response->assertOk();

        $response->assertDontSee('Larger text');
        $response->assertDontSee('High contrast');
        $response->assertDontSee('largeTextMode', false);
        $response->assertDontSee('highContrastMode', false);
    }
}