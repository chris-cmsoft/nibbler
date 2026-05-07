<?php

namespace App\Support;

use App\Models\Animal;
use Illuminate\Support\Facades\Vite;

class AnimalPayload
{
    /**
     * Transform an animal into the frontend payload shape.
     *
     * @return array{
     *     id: int,
     *     name: string,
     *     caloriesPerDay: int,
     *     attentionPoints: int,
     *     svgPath: string,
     *     svgUrl: string
     * }
     */
    public static function fromAnimal(Animal $animal): array
    {
        return [
            'id' => $animal->id,
            'name' => $animal->name,
            'caloriesPerDay' => $animal->calories_per_day,
            'attentionPoints' => $animal->attention_points,
            'svgPath' => $animal->svg_path,
            'svgUrl' => Vite::asset($animal->svg_path),
        ];
    }
}
