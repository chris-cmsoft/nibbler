<?php

namespace App\Jobs;

use App\Events\PetCareUpdated;
use App\Models\Pet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class FeedPet implements ShouldQueue
{
    use Queueable;

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
        public int $calories = 10,
        public int $durationSeconds = 180,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $calories = max(1, $this->calories);
        $intervalSeconds = $this->durationSeconds > 0
            ? max(1, (int) round($this->durationSeconds / $calories))
            : 0;

        for ($calorie = 0; $calorie < $calories; $calorie++) {
            if ($intervalSeconds > 0) {
                sleep($intervalSeconds);
            }

            $pet = $this->feedOneCalorie();

            if (! $pet) {
                return;
            }

            broadcast(new PetCareUpdated(
                petId: $pet->id,
                calorieLevel: $pet->calorie_level,
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
                ->whereKey($this->petId)
                ->lockForUpdate()
                ->first();

            if (! $pet) {
                return null;
            }

            $pet->update([
                'calorie_level' => min(100, $pet->calorie_level + 1),
            ]);

            return $pet;
        });
    }
}
