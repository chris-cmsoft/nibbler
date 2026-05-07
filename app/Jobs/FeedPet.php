<?php

namespace App\Jobs;

use App\Events\PetCareUpdated;
use App\Models\Pet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedPet implements ShouldQueue
{
    use Queueable;

    private bool $overfeedingLogged = false;

    /**
     * The number of seconds the job may run before timing out.
     */
    public int $timeout = 240;

    /**
     * Feeding should never be retried because a second attempt can overfeed.
     */
    public int $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $petId,
        public int $calories = 100,
        public int $durationSeconds = 60,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $calories = max(1, $this->calories);
        $intervalMicroseconds = $this->durationSeconds > 0
            ? max(1, (int) round(($this->durationSeconds / $calories) * 1_000_000))
            : 0;

        for ($calorie = 0; $calorie < $calories; $calorie++) {
            if ($intervalMicroseconds > 0) {
                usleep($intervalMicroseconds);
            }

            $pet = $this->feedOneCalorie();

            if (! $pet) {
                return;
            }

            broadcast(new PetCareUpdated(
                petId: $pet->id,
                calorieLevel: (float) $pet->calorie_level,
                attentionLevel: $pet->attention_level,
            ));
        }
    }

    /**
     * Feed the pet a single calorie while safely handling concurrent updates.
     */
    private function feedOneCalorie(): ?Pet
    {
        return DB::transaction(function (): ?Pet {
            $pet = Pet::query()
                ->with('animal')
                ->whereKey($this->petId)
                ->lockForUpdate()
                ->first();

            if (! $pet) {
                return null;
            }

            if ($pet->calorie_level >= $pet->animal->calories_per_day) {
                $this->logOverfeeding($pet);
            }

            $pet->update([
                'calorie_level' => round(min($pet->animal->calories_per_day, $pet->calorie_level + 1), 4),
            ]);

            return $pet;
        });
    }

    private function logOverfeeding(Pet $pet): void
    {
        if ($this->overfeedingLogged) {
            return;
        }

        Log::warning('Pet is full, but continued to be fed.', [
            'pet_id' => $pet->id,
            'pet_name' => $pet->name,
            'team_id' => $pet->team_id,
            'animal_id' => $pet->animal_id,
            'animal_name' => $pet->animal->name,
            'calorie_level' => $pet->calorie_level,
            'daily_calories' => $pet->animal->calories_per_day,
            'requested_calories' => $this->calories,
        ]);

        $this->overfeedingLogged = true;
    }
}
