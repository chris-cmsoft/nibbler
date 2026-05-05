<?php

namespace Tests\Feature\Pets;

use App\Enums\TeamRole;
use App\Models\Pet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PetCareTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_team_members_can_view_their_pets(): void
    {
        $this->withoutVite();

        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create(['name' => 'Nibble']);
        Pet::factory()->create(['name' => 'Other Team Pet']);

        $response = $this
            ->actingAs($user)
            ->get(route('pets.index', ['current_team' => $team]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('pets/Index')
                ->has('pets', 1)
                ->where('pets.0.id', $pet->id)
                ->where('pets.0.name', 'Nibble')
            );
    }

    public function test_guests_are_redirected_from_the_pets_page(): void
    {
        $team = Team::factory()->create();

        $response = $this->get(route('pets.index', ['current_team' => $team]));

        $response->assertRedirect(route('login'));
    }

    public function test_non_team_members_cannot_view_team_pets(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('pets.index', ['current_team' => $team]));

        $response->assertForbidden();
    }

    public function test_feed_increments_calories_by_ten(): void
    {
        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create(['calorie_level' => 50]);

        $response = $this
            ->actingAs($user)
            ->post(route('pets.feed', ['current_team' => $team, 'pet' => $pet]));

        $response->assertRedirect();

        $this->assertSame(60, $pet->fresh()->calorie_level);
    }

    public function test_feed_caps_calories_at_one_hundred(): void
    {
        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create(['calorie_level' => 95]);

        $this
            ->actingAs($user)
            ->post(route('pets.feed', ['current_team' => $team, 'pet' => $pet]));

        $this->assertSame(100, $pet->fresh()->calorie_level);
    }

    public function test_pet_increments_stimulation_by_ten(): void
    {
        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create(['attention_level' => 50]);

        $response = $this
            ->actingAs($user)
            ->post(route('pets.pet', ['current_team' => $team, 'pet' => $pet]));

        $response->assertRedirect();

        $this->assertSame(60, $pet->fresh()->attention_level);
    }

    public function test_pet_caps_stimulation_at_one_hundred(): void
    {
        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create(['attention_level' => 95]);

        $this
            ->actingAs($user)
            ->post(route('pets.pet', ['current_team' => $team, 'pet' => $pet]));

        $this->assertSame(100, $pet->fresh()->attention_level);
    }

    public function test_pet_actions_cannot_mutate_another_teams_pet(): void
    {
        [$user, $team] = $this->userAndTeam();
        $otherPet = Pet::factory()->create(['calorie_level' => 50]);

        $response = $this
            ->actingAs($user)
            ->post(route('pets.feed', ['current_team' => $team, 'pet' => $otherPet]));

        $response->assertNotFound();

        $this->assertSame(50, $otherPet->fresh()->calorie_level);
    }

    /**
     * @return array{0: User, 1: Team}
     */
    private function userAndTeam(): array
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $team->members()->attach($user, ['role' => TeamRole::Owner->value]);
        $user->switchTeam($team);

        return [$user, $team];
    }
}
