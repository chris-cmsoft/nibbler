<?php

namespace App\Jobs;

use App\Events\PetCareUpdated;
use App\Models\Pet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ConsumePetCalories implements ShouldQueue
{
    use Queueable;

    public const int CONSUMPTION_INTERVAL_SECONDS = 10;

    /**
     * Calorie consumption should retry so a failed tick can still be applied.
     */
    public int $tries = 3;

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['consume'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Pet::query()
            ->with('animal')
            ->where('calorie_level', '>', 0)
            ->chunkById(100, function ($pets): void {
                $pets->each(fn (Pet $pet) => $this->consumeCalories($pet));
            });
    }

    /**
     * Consume one scheduled interval of calories for a pet.
     */
    private function consumeCalories(Pet $pet): void
    {
        $pet = DB::transaction(function () use ($pet): ?Pet {
            $pet = Pet::query()
                ->with('animal')
                ->whereKey($pet->id)
                ->lockForUpdate()
                ->first();

            if (! $pet || $pet->calorie_level <= 0) {
                return null;
            }

            $caloriesPerInterval = ($pet->animal->calories_per_day / 86_400) * self::CONSUMPTION_INTERVAL_SECONDS;

            $pet->update([
                'calorie_level' => round(max(0, $pet->calorie_level - $caloriesPerInterval), 4),
            ]);

            return $pet;
        });

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
