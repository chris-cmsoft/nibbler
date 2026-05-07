<?php

namespace Database\Seeders;

use App\Models\Animal;
use Illuminate\Database\Seeder;

class AnimalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $animals = [
            [
                'slug' => 'mouse',
                'name' => 'Mouse',
                'calories_per_day' => 30,
                'attention_points' => 10,
                'svg_path' => 'resources/animals/mouse.svg',
            ],
            [
                'slug' => 'elephpant',
                'name' => 'ElePHPant',
                'calories_per_day' => 70000,
                'attention_points' => 50,
                'svg_path' => 'resources/animals/elephpant.svg',
            ],
            [
                'slug' => 'pug',
                'name' => 'Pug',
                'calories_per_day' => 600,
                'attention_points' => 60000,
                'svg_path' => 'resources/animals/pug.svg',
            ],
        ];

        foreach ($animals as $animal) {
            Animal::updateOrCreate(
                ['slug' => $animal['slug']],
                $animal,
            );
        }
    }
}
