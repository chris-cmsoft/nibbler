<?php

namespace Tests\Feature\Seeders;

use App\Models\Animal;
use Database\Seeders\AnimalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnimalSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_animal_seeder_creates_the_bundled_animals(): void
    {
        $this->seed(AnimalSeeder::class);

        $this->assertDatabaseCount('animals', 3);
        $this->assertDatabaseHas('animals', [
            'slug' => 'mouse',
            'name' => 'Mouse',
            'calories_per_day' => 30,
            'attention_points' => 10,
            'svg_path' => 'resources/animals/mouse.svg',
        ]);
        $this->assertDatabaseHas('animals', [
            'slug' => 'elephpant',
            'name' => 'ElePHPant',
            'calories_per_day' => 70000,
            'attention_points' => 50,
            'svg_path' => 'resources/animals/elephpant.svg',
        ]);
        $this->assertDatabaseHas('animals', [
            'slug' => 'pug',
            'name' => 'Pug',
            'calories_per_day' => 600,
            'attention_points' => 60000,
            'svg_path' => 'resources/animals/pug.svg',
        ]);
    }

    public function test_animal_seeder_updates_animals_by_slug(): void
    {
        Animal::factory()->create([
            'slug' => 'mouse',
            'name' => 'Old Mouse',
            'calories_per_day' => 5,
            'attention_points' => 5,
            'svg_path' => 'resources/animals/old-mouse.svg',
        ]);
        Animal::factory()->create([
            'slug' => 'elephpant',
            'name' => 'Old Elephant',
            'calories_per_day' => 10,
            'attention_points' => 10,
            'svg_path' => 'resources/animals/old-elephant.svg',
        ]);
        Animal::factory()->create([
            'slug' => 'pug',
            'name' => 'Old Pug',
            'calories_per_day' => 15,
            'attention_points' => 15,
            'svg_path' => 'resources/animals/old-pug.svg',
        ]);

        $this->seed(AnimalSeeder::class);

        $this->assertDatabaseCount('animals', 3);
        $this->assertDatabaseHas('animals', [
            'slug' => 'mouse',
            'name' => 'Mouse',
            'calories_per_day' => 30,
            'attention_points' => 10,
            'svg_path' => 'resources/animals/mouse.svg',
        ]);
        $this->assertDatabaseHas('animals', [
            'slug' => 'elephpant',
            'name' => 'ElePHPant',
            'calories_per_day' => 70000,
            'attention_points' => 50,
            'svg_path' => 'resources/animals/elephpant.svg',
        ]);
        $this->assertDatabaseHas('animals', [
            'slug' => 'pug',
            'name' => 'Pug',
            'calories_per_day' => 600,
            'attention_points' => 60000,
            'svg_path' => 'resources/animals/pug.svg',
        ]);
    }
}
