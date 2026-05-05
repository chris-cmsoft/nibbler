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
        Animal::updateOrCreate(
            ['slug' => 'mouse'],
            [
                'name' => 'Mouse',
                'calories_per_day' => 300,
                'attention_points' => 50,
                'svg_path' => 'resources/animals/mouse.svg',
            ],
        );
    }
}
