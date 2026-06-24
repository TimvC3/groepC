<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;


class EndToEndTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_login_and_create_function(): void
    {
        $category = Category::first();

        $adminEmail = 'admin@example.com';

        $this->browse(function (Browser $browser) use ($adminEmail, $category) {

            $browser->visit('/login')
                ->assertSee('Email')
                ->assertSee('LOG IN')
                ->type('email', $adminEmail)
                ->type('password', 'Password')
                ->press('LOG IN')
                ->waitForText('Metropolis Grid', 10)
                ->assertSee('Metropolis Grid')
                ->clickLink('Functions')
                ->waitForText('Create Function', 10)
                ->assertSee('Create Function')
                ->type('name', 'Public Library')
                ->select('category_id', $category->id);

            $browser->script("
                const input = document.querySelector('[name=\"icon\"]');
                input.value = '🏛️';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            ");

            $browser
                ->type('scores[' . $category->id . ']', '3')
                ->press('Save Function')
                ->waitForText('Public Library', 10)
                ->assertSee('Public Library');
        });
    }
}