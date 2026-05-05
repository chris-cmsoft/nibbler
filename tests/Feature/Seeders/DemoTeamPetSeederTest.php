<?php

namespace Tests\Feature\Seeders;

use App\Models\Pet;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\DemoTeamPetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoTeamPetSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_users_shared_team_and_mouse_pet(): void
    {
        $this->seed(DemoTeamPetSeeder::class);

        $team = Team::where('slug', 'nibbler-demo')->firstOrFail();

        collect([1, 2, 3])->each(function (int $number) use ($team): void {
            $user = User::where('email', "user{$number}@example.com")->firstOrFail();

            $this->assertTrue(Hash::check('password123', $user->password));
            $this->assertSame($team->id, $user->current_team_id);
            $this->assertTrue($user->belongsToTeam($team));
        });

        $pet = Pet::with('animal')->whereBelongsTo($team)->sole();

        $this->assertSame('Nibble', $pet->name);
        $this->assertSame(50.0, $pet->calorie_level);
        $this->assertSame(50, $pet->attention_level);
        $this->assertSame('mouse', $pet->animal->slug);
    }
}
