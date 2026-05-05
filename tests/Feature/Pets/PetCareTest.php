<?php

namespace Tests\Feature\Pets;

use App\Enums\TeamRole;
use App\Events\PetCareUpdated;
use App\Events\PetReturned;
use App\Jobs\FeedPet;
use App\Models\Pet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
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

    public function test_feed_queues_a_slow_feeding_job(): void
    {
        Queue::fake();

        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create(['calorie_level' => 50]);

        $response = $this
            ->actingAs($user)
            ->post(route('pets.feed', ['current_team' => $team, 'pet' => $pet]));

        $response->assertRedirect();

        $this->assertSame(50, $pet->fresh()->calorie_level);
        Queue::assertPushed(FeedPet::class, fn (FeedPet $job) => $job->petId === $pet->id
            && $job->calories === 100
            && $job->durationSeconds === 60);
    }

    public function test_feed_action_does_not_run_the_job_inline(): void
    {
        Queue::fake();

        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create(['calorie_level' => 95]);

        $this
            ->actingAs($user)
            ->post(route('pets.feed', ['current_team' => $team, 'pet' => $pet]));

        $this->assertSame(95, $pet->fresh()->calorie_level);
        Queue::assertPushed(FeedPet::class);
    }

    public function test_pet_increments_stimulation_by_ten(): void
    {
        Event::fake([PetCareUpdated::class]);

        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create(['attention_level' => 50]);

        $response = $this
            ->actingAs($user)
            ->post(route('pets.pet', ['current_team' => $team, 'pet' => $pet]));

        $response->assertRedirect();

        $this->assertSame(60, $pet->fresh()->attention_level);
        Event::assertDispatched(PetCareUpdated::class, fn (PetCareUpdated $event) => $event->petId === $pet->id
            && $event->calorieLevel === $pet->calorie_level
            && $event->attentionLevel === 60);
    }

    public function test_team_members_can_authorize_pet_private_channels(): void
    {
        $this->useReverbBroadcasting();

        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create();

        $response = $this
            ->actingAs($user)
            ->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => "private-pets.{$pet->id}",
            ]);

        $response->assertOk();
    }

    public function test_non_team_members_cannot_authorize_pet_private_channels(): void
    {
        $this->useReverbBroadcasting();

        $user = User::factory()->create();
        $pet = Pet::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => "private-pets.{$pet->id}",
            ]);

        $response->assertForbidden();
    }

    public function test_team_members_can_authorize_team_private_channels(): void
    {
        $this->useReverbBroadcasting();

        [$user, $team] = $this->userAndTeam();

        $response = $this
            ->actingAs($user)
            ->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => "private-teams.{$team->id}",
            ]);

        $response->assertOk();
    }

    public function test_non_team_members_cannot_authorize_team_private_channels(): void
    {
        $this->useReverbBroadcasting();

        $user = User::factory()->create();
        $team = Team::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/broadcasting/auth', [
                'socket_id' => '1234.5678',
                'channel_name' => "private-teams.{$team->id}",
            ]);

        $response->assertForbidden();
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

    public function test_team_members_can_return_a_pet(): void
    {
        Event::fake([PetReturned::class]);

        [$user, $team] = $this->userAndTeam();
        $pet = Pet::factory()->for($team)->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('pets.destroy', ['current_team' => $team, 'pet' => $pet]));

        $response->assertRedirect();
        $this->assertModelMissing($pet);
        Event::assertDispatched(PetReturned::class, fn (PetReturned $event) => $event->teamId === $team->id
            && $event->petId === $pet->id
            && $event->actorName === $user->name
            && $event->petName === $pet->name);
    }

    public function test_return_pet_cannot_delete_another_teams_pet(): void
    {
        [$user, $team] = $this->userAndTeam();
        $otherPet = Pet::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('pets.destroy', ['current_team' => $team, 'pet' => $otherPet]));

        $response->assertNotFound();
        $this->assertModelExists($otherPet);
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

    private function useReverbBroadcasting(): void
    {
        Config::set('broadcasting.default', 'reverb');
        Config::set('broadcasting.connections.reverb.key', 'testing-key');
        Config::set('broadcasting.connections.reverb.secret', 'testing-secret');
        Config::set('broadcasting.connections.reverb.app_id', 'testing-app');

        Broadcast::forgetDrivers();
        Broadcast::channel('pets.{pet}', fn (User $user, Pet $pet): bool => $user->teams()->whereKey($pet->team_id)->exists());
        Broadcast::channel('teams.{teamId}', fn (User $user, int $teamId): bool => $user->teams()->whereKey($teamId)->exists());
    }
}
