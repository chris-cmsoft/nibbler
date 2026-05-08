<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorizonAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_horizon(): void
    {
        $this->get('/horizon')
            ->assertForbidden();
    }

    public function test_non_admin_users_cannot_access_horizon(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/horizon')
            ->assertForbidden();
    }

    public function test_admin_users_can_access_horizon(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/horizon')
            ->assertOk();
    }
}
