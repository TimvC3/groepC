<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Bes3aFacilityAccessTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_can_open_facility_management_from_navigation(): void
    {
        $this->assertRoleCanOpenFacilityManagement('admin');
    }

    public function test_library_manager_can_open_facility_management_from_navigation(): void
    {
        $this->assertRoleCanOpenFacilityManagement('library_manager');
    }

    private function assertRoleCanOpenFacilityManagement(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/grid')
                ->waitForLink('Facilities', 5)
                ->assertSeeLink('Facilities')
                ->assertDontSeeLink('Events')
                ->clickLink('Facilities')
                ->waitForLocation('/facilities', 5)
                ->assertPathIs('/facilities')
                ->waitForText('Existing Facilities', 5)
                ->assertSee('Create Facility')
                ->assertSee('Facility Score Matrix');
        });
    }
}
