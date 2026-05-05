<?php

namespace Tests\Feature\Pets;

use App\Enums\TeamRole;
use App\Events\PetAdopted;
use App\Models\Animal;
use App\Models\Pet;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdoptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_team_members_can_view_all_available_animals(): void
    {
        $this->withoutVite();

        [$user, $team] = $this->userAndTeam();
        $mouse = $this->animal(['name' => 'Mouse']);
        $rabbit = $this->animal(['name' => 'Rabbit']);

        Pet::factory()->for($team)->for($mouse)->create();

        $response = $this
            ->actingAs($user)
            ->get(route('adoptions.index', ['current_team' => $team]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('adoptions/Index')
                ->has('animals', 2)
                ->where('animals.0.id', $mouse->id)
                ->where('animals.0.name', 'Mouse')
                ->where('animals.1.id', $rabbit->id)
                ->where('animals.1.name', 'Rabbit')
            );
    }

    public function test_guests_are_redirected_from_the_adoptions_page(): void
    {
        $team = Team::factory()->create();

        $response = $this->get(route('adoptions.index', ['current_team' => $team]));

        $response->assertRedirect(route('login'));
    }

    public function test_non_team_members_cannot_view_adoptions(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('adoptions.index', ['current_team' => $team]));

        $response->assertForbidden();
    }

    public function test_team_members_can_adopt_an_animal(): void
    {
        $this->withoutVite();
        Event::fake([PetAdopted::class]);

        [$user, $team] = $this->userAndTeam();
        $animal = $this->animal(['name' => 'Mouse']);

        $response = $this
            ->actingAs($user)
            ->post(route('adoptions.store', [
                'current_team' => $team,
                'animal' => $animal,
            ]), [
                'name' => 'Squeaky',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pets', [
            'team_id' => $team->id,
            'animal_id' => $animal->id,
            'name' => 'Squeaky',
            'calorie_level' => 50,
            'attention_level' => 50,
        ]);

        $pet = Pet::whereBelongsTo($team)->whereBelongsTo($animal)->firstOrFail();

        Event::assertDispatched(PetAdopted::class, fn (PetAdopted $event) => $event->teamId === $team->id
            && $event->pet['id'] === $pet->id
            && $event->pet['name'] === 'Squeaky'
            && $event->pet['animal']['name'] === 'Mouse'
            && $event->actorName === $user->name
            && $event->petName === 'Squeaky');
    }

    public function test_pet_name_is_required_for_adoption(): void
    {
        [$user, $team] = $this->userAndTeam();
        $animal = $this->animal();

        $response = $this
            ->actingAs($user)
            ->post(route('adoptions.store', [
                'current_team' => $team,
                'animal' => $animal,
            ]), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('pets', [
            'team_id' => $team->id,
            'animal_id' => $animal->id,
        ]);
    }

    public function test_pet_name_may_not_be_longer_than_fifty_characters(): void
    {
        [$user, $team] = $this->userAndTeam();
        $animal = $this->animal();

        $response = $this
            ->actingAs($user)
            ->post(route('adoptions.store', [
                'current_team' => $team,
                'animal' => $animal,
            ]), [
                'name' => str_repeat('a', 51),
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('pets', [
            'team_id' => $team->id,
            'animal_id' => $animal->id,
        ]);
    }

    public function test_pet_name_must_be_unique_for_the_team(): void
    {
        [$user, $team] = $this->userAndTeam();
        $animal = $this->animal();

        Pet::factory()->for($team)->create(['name' => 'Squeaky']);

        $response = $this
            ->actingAs($user)
            ->post(route('adoptions.store', [
                'current_team' => $team,
                'animal' => $animal,
            ]), [
                'name' => 'Squeaky',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertSame(1, Pet::whereBelongsTo($team)->where('name', 'Squeaky')->count());
    }

    public function test_pet_name_may_be_reused_by_another_team(): void
    {
        [$user, $team] = $this->userAndTeam();
        $animal = $this->animal();

        Pet::factory()->create(['name' => 'Squeaky']);

        $response = $this
            ->actingAs($user)
            ->post(route('adoptions.store', [
                'current_team' => $team,
                'animal' => $animal,
            ]), [
                'name' => 'Squeaky',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pets', [
            'team_id' => $team->id,
            'name' => 'Squeaky',
        ]);
    }

    public function test_an_animal_can_be_adopted_many_times_by_the_same_team(): void
    {
        $this->withoutVite();

        [$user, $team] = $this->userAndTeam();
        $animal = $this->animal();

        $this
            ->actingAs($user)
            ->post(route('adoptions.store', ['current_team' => $team, 'animal' => $animal]), [
                'name' => 'First Mouse',
            ]);

        $this
            ->actingAs($user)
            ->post(route('adoptions.store', ['current_team' => $team, 'animal' => $animal]), [
                'name' => 'Second Mouse',
            ]);

        $this->assertSame(2, Pet::whereBelongsTo($team)->whereBelongsTo($animal)->count());
    }

    public function test_non_team_members_cannot_adopt_for_another_team(): void
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $animal = $this->animal();

        $response = $this
            ->actingAs($user)
            ->post(route('adoptions.store', [
                'current_team' => $team,
                'animal' => $animal,
            ]), [
                'name' => 'Squeaky',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('pets', [
            'team_id' => $team->id,
            'animal_id' => $animal->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function animal(array $attributes = []): Animal
    {
        return Animal::factory()->create([
            'svg_path' => 'resources/animals/mouse.svg',
            ...$attributes,
        ]);
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
