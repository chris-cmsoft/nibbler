<?php

namespace App\Http\Controllers;

use App\Events\PetCareUpdated;
use App\Models\Pet;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Vite;
use Inertia\Inertia;
use Inertia\Response;

class PetController extends Controller
{
    /**
     * Display the team's pets.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('pets/Index', [
            'pets' => $currentTeam->pets()
                ->with('animal')
                ->orderBy('name')
                ->get()
                ->map(fn (Pet $pet) => [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'birthday' => $pet->birthday->toDateString(),
                    'calorieLevel' => $pet->calorie_level,
                    'attentionLevel' => $pet->attention_level,
                    'animal' => [
                        'name' => $pet->animal->name,
                        'caloriesPerDay' => $pet->animal->calories_per_day,
                        'attentionPoints' => $pet->animal->attention_points,
                        'svgPath' => $pet->animal->svg_path,
                        'svgUrl' => Vite::asset($pet->animal->svg_path),
                    ],
                ]),
        ]);
    }

    /**
     * Feed the given pet.
     */
    public function feed(Team $currentTeam, Pet $pet): RedirectResponse
    {
        $pet = $this->incrementCareLevel($currentTeam, $pet, 'calorie_level');

        broadcast(new PetCareUpdated(
            petId: $pet->id,
            calorieLevel: $pet->calorie_level,
            attentionLevel: $pet->attention_level,
        ));

        return back();
    }

    /**
     * Give attention to the given pet.
     */
    public function pet(Team $currentTeam, Pet $pet): RedirectResponse
    {
        $pet = $this->incrementCareLevel($currentTeam, $pet, 'attention_level');

        broadcast(new PetCareUpdated(
            petId: $pet->id,
            calorieLevel: $pet->calorie_level,
            attentionLevel: $pet->attention_level,
        ));

        return back();
    }

    /**
     * Increment one of the pet's care counters.
     */
    private function incrementCareLevel(Team $team, Pet $pet, string $column): Pet
    {
        return DB::transaction(function () use ($team, $pet, $column): Pet {
            $pet = $team->pets()
                ->whereKey($pet->id)
                ->lockForUpdate()
                ->firstOrFail();

            $pet->update([
                $column => min(100, $pet->{$column} + 10),
            ]);

            return $pet;
        });
    }
}
