<?php

namespace Tests\Feature\Jobs;

use App\Events\PetCareUpdated;
use App\Jobs\FeedPet;
use App\Models\Animal;
use App\Models\Pet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class FeedPetTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_pet_job_is_not_retried(): void
    {
        $job = new FeedPet(petId: 1);

        $this->assertSame(1, $job->tries);
    }

    public function test_feed_pet_job_is_tagged_for_horizon(): void
    {
        $job = new FeedPet(petId: 1);

        $this->assertSame(['feed'], $job->tags());
    }

    public function test_feed_pet_adds_calories_one_at_a_time(): void
    {
        Event::fake([PetCareUpdated::class]);

        $animal = Animal::factory()->create(['calories_per_day' => 300]);
        $pet = Pet::factory()->create([
            'animal_id' => $animal->id,
            'calorie_level' => 50,
            'attention_level' => 25,
        ]);

        (new FeedPet($pet->id, calories: 3, durationSeconds: 0))->handle();

        $this->assertSame(53.0, $pet->fresh()->calorie_level);
        Event::assertDispatchedTimes(PetCareUpdated::class, 3);
        Event::assertDispatched(PetCareUpdated::class, fn (PetCareUpdated $event) => $event->petId === $pet->id
            && $event->calorieLevel === 53.0
            && $event->attentionLevel === 25);
    }

    public function test_feed_pet_caps_calories_at_the_animals_daily_need(): void
    {
        Event::fake([PetCareUpdated::class]);

        $animal = Animal::factory()->create(['calories_per_day' => 120]);
        $pet = Pet::factory()->for($animal)->create(['calorie_level' => 118]);

        (new FeedPet($pet->id, calories: 10, durationSeconds: 0))->handle();

        $this->assertSame(120.0, $pet->fresh()->calorie_level);
    }

    public function test_feed_pet_logs_overfeeding_once_per_feeding(): void
    {
        Event::fake([PetCareUpdated::class]);

        $animal = Animal::factory()->create(['calories_per_day' => 120]);
        $pet = Pet::factory()->for($animal)->create(['calorie_level' => 120]);

        Log::shouldReceive('warning')
            ->once()
            ->with('Pet is full, but continued to be fed.', Mockery::on(fn (array $context): bool => $context['pet_id'] === $pet->id
                && $context['pet_name'] === $pet->name
                && $context['team_id'] === $pet->team_id
                && $context['animal_id'] === $animal->id
                && $context['animal_name'] === $animal->name
                && $context['calorie_level'] === 120.0
                && $context['daily_calories'] === 120
                && $context['requested_calories'] === 3));

        (new FeedPet($pet->id, calories: 3, durationSeconds: 0))->handle();

        $this->assertSame(120.0, $pet->fresh()->calorie_level);
    }

    public function test_feed_pet_exits_when_the_pet_has_been_returned(): void
    {
        Event::fake([PetCareUpdated::class]);

        $pet = Pet::factory()->create(['calorie_level' => 50]);
        $petId = $pet->id;

        $pet->delete();

        (new FeedPet($petId, calories: 10, durationSeconds: 0))->handle();

        Event::assertNotDispatched(PetCareUpdated::class);
    }
}
