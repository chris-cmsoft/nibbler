<?php

namespace Tests\Feature\Seeders;

use App\Models\Animal;
use Database\Seeders\AnimalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_animal_seeder_creates_the_mouse(): void
    {
        $this->seed(AnimalSeeder::class);

        $this->assertDatabaseHas('animals', [
            'slug' => 'mouse',
            'name' => 'Mouse',
            'calories_per_day' => 100,
            'attention_points' => 50,
            'svg_path' => '/animals/mouse.svg',
        ]);
    }

    public function test_animal_seeder_updates_the_mouse_by_slug(): void
    {
        Animal::factory()->create([
            'slug' => 'mouse',
            'name' => 'Old Mouse',
            'calories_per_day' => 5,
            'attention_points' => 5,
            'svg_path' => '/animals/old-mouse.svg',
        ]);

        $this->seed(AnimalSeeder::class);

        $this->assertDatabaseCount('animals', 1);
        $this->assertDatabaseHas('animals', [
            'slug' => 'mouse',
            'name' => 'Mouse',
            'calories_per_day' => 100,
            'attention_points' => 50,
            'svg_path' => '/animals/mouse.svg',
        ]);
    }
}
