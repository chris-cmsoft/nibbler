<?php

namespace Tests\Feature\Jobs;

use App\Events\PetCareUpdated;
use App\Jobs\ConsumePetCalories;
use App\Models\Animal;
use App\Models\Pet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ConsumePetCaloriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_consume_pet_calories_job_retries_on_failure(): void
    {
        $job = new ConsumePetCalories;

        $this->assertSame(3, $job->tries);
    }

    public function test_consume_pet_calories_job_is_tagged_for_horizon(): void
    {
        $job = new ConsumePetCalories;

        $this->assertSame(['consume'], $job->tags());
    }

    public function test_consume_pet_calories_uses_one_scheduled_interval_of_daily_calories(): void
    {
        Event::fake([PetCareUpdated::class]);

        $animal = Animal::factory()->create(['calories_per_day' => 300]);
        $pet = Pet::factory()->for($animal)->create([
            'calorie_level' => 100,
            'attention_level' => 25,
        ]);

        (new ConsumePetCalories)->handle();

        $this->assertEqualsWithDelta(99.9653, $pet->fresh()->calorie_level, 0.0001);
        Event::assertDispatched(PetCareUpdated::class, fn (PetCareUpdated $event) => $event->petId === $pet->id
            && abs($event->calorieLevel - 99.9653) < 0.0001
            && $event->attentionLevel === 25);
    }

    public function test_consume_pet_calories_does_not_go_below_zero(): void
    {
        Event::fake([PetCareUpdated::class]);

        $animal = Animal::factory()->create(['calories_per_day' => 300]);
        $pet = Pet::factory()->for($animal)->create(['calorie_level' => 0.01]);

        (new ConsumePetCalories)->handle();

        $this->assertSame(0.0, $pet->fresh()->calorie_level);
    }

    public function test_consume_pet_calories_allows_overlapping_runs(): void
    {
        $this->assertFalse(method_exists(new ConsumePetCalories, 'middleware'));
    }
}
