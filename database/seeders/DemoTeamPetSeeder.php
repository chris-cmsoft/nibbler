<?php

namespace Database\Seeders;

use App\Enums\TeamRole;
use App\Models\Animal;
use App\Models\Pet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTeamPetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(AnimalSeeder::class);

        $team = Team::updateOrCreate(
            ['slug' => 'nibbler-demo'],
            [
                'name' => 'Nibbler Demo',
                'is_personal' => false,
            ],
        );

        collect([1, 2, 3])->each(function (int $number) use ($team): void {
            $user = User::updateOrCreate(
                ['email' => "user{$number}@example.com"],
                [
                    'name' => "User {$number}",
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'current_team_id' => $team->id,
                ],
            );

            $team->members()->syncWithoutDetaching([
                $user->id => [
                    'role' => $number === 1 ? TeamRole::Owner->value : TeamRole::Member->value,
                ],
            ]);

            $user->switchTeam($team);
        });

        Pet::updateOrCreate(
            [
                'team_id' => $team->id,
                'animal_id' => Animal::where('slug', 'mouse')->value('id'),
                'name' => 'Nibble',
            ],
            [
                'calorie_level' => 50,
                'attention_level' => 50,
                'birthday' => now()->toDateString(),
            ],
        );
    }
}
