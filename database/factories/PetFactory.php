<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Pet;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pet>
 */
class PetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'animal_id' => Animal::factory(),
            'name' => fake()->firstName(),
            'calorie_level' => fake()->numberBetween(0, 300),
            'attention_level' => fake()->numberBetween(0, 100),
            'birthday' => fake()->dateTimeBetween('-2 years')->format('Y-m-d'),
        ];
    }
}
