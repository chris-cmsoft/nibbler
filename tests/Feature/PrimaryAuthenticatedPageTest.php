<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrimaryAuthenticatedPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_primary_authenticated_page_to_login()
    {
        $user = User::factory()->create();

        $response = $this->get(route('pets.redirect'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_are_redirected_to_their_team_pets()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('pets.redirect'));

        $response->assertRedirect(route('pets.index', ['current_team' => $user->currentTeam]));
    }
}
