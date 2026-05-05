<?php

namespace App\Support;

use App\Models\Pet;
use Illuminate\Support\Facades\Vite;

class PetPayload
{
    /**
     * Transform a pet into the frontend payload shape.
     *
     * @return array{
     *     id: int,
     *     name: string,
     *     birthday: string,
     *     calorieLevel: float,
     *     attentionLevel: int,
     *     animal: array{
     *         id: int,
     *         name: string,
     *         caloriesPerDay: int,
     *         attentionPoints: int,
     *         svgPath: string,
     *         svgUrl: string
     *     }
     * }
     */
    public static function fromPet(Pet $pet): array
    {
        $pet->loadMissing('animal');

        return [
            'id' => $pet->id,
            'name' => $pet->name,
            'birthday' => $pet->birthday->toDateString(),
            'calorieLevel' => (float) $pet->calorie_level,
            'attentionLevel' => $pet->attention_level,
            'animal' => [
                'id' => $pet->animal->id,
                'name' => $pet->animal->name,
                'caloriesPerDay' => $pet->animal->calories_per_day,
                'attentionPoints' => $pet->animal->attention_points,
                'svgPath' => $pet->animal->svg_path,
                'svgUrl' => Vite::asset($pet->animal->svg_path),
            ],
        ];
    }
}
