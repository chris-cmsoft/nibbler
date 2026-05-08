<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeUserAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_promotes_a_user_to_admin_by_email(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->artisan('user:make-admin', [
            'email' => $user->email,
        ])
            ->expectsOutput("User [{$user->email}] is now an administrator.")
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_it_succeeds_when_the_user_is_already_an_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->artisan('user:make-admin', [
            'email' => $admin->email,
        ])
            ->expectsOutput("User [{$admin->email}] is already an administrator.")
            ->assertSuccessful();

        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_it_fails_when_no_user_exists_with_the_email(): void
    {
        $this->artisan('user:make-admin', [
            'email' => 'missing@example.com',
        ])
            ->expectsOutput('No user found with email [missing@example.com].')
            ->assertFailed();
    }
}
