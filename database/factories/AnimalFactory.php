<?php

namespace Database\Factories;

use App\Models\Animal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Animal>
 */
class AnimalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'slug' => Str::slug($name),
            'name' => ucfirst($name),
            'calories_per_day' => fake()->numberBetween(50, 200),
            'attention_points' => fake()->numberBetween(25, 100),
            'svg_path' => '/animals/'.Str::slug($name).'.svg',
        ];
    }
}
