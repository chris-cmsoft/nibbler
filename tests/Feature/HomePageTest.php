<?php

namespace Tests\Feature;

use App\Models\Animal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_available_adoptions(): void
    {
        $this->withoutVite();

        $mouse = Animal::factory()->create(['name' => 'Mouse']);
        $rabbit = Animal::factory()->create(['name' => 'Rabbit']);

        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('animals', 2)
                ->where('animals.0.id', $mouse->id)
                ->where('animals.0.name', 'Mouse')
                ->where('animals.1.id', $rabbit->id)
                ->where('animals.1.name', 'Rabbit')
                ->where('canRegister', true)
            );
    }
}
