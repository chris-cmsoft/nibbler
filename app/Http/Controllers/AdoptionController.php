<?php

namespace App\Http\Controllers;

use App\Events\PetAdopted;
use App\Models\Animal;
use App\Models\Team;
use App\Support\PetPayload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdoptionController extends Controller
{
    /**
     * Display all animals available for adoption.
     */
    public function index(Team $currentTeam): Response
    {
        return Inertia::render('adoptions/Index', [
            'animals' => Animal::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Animal $animal) => [
                    'id' => $animal->id,
                    'name' => $animal->name,
                    'caloriesPerDay' => $animal->calories_per_day,
                    'attentionPoints' => $animal->attention_points,
                    'svgPath' => $animal->svg_path,
                    'svgUrl' => Vite::asset($animal->svg_path),
                ]),
        ]);
    }

    /**
     * Adopt an animal into the current team.
     */
    public function store(Request $request, Team $currentTeam, Animal $animal): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pets')->where(fn ($query) => $query->where('team_id', $currentTeam->id)),
            ],
        ]);

        $pet = $currentTeam->pets()->create([
            'animal_id' => $animal->id,
            'name' => $validated['name'],
            'calorie_level' => 50,
            'attention_level' => 50,
            'birthday' => now()->toDateString(),
        ]);

        broadcast(new PetAdopted(
            teamId: $currentTeam->id,
            pet: PetPayload::fromPet($pet),
            actorName: $request->user()->name,
            petName: $pet->name,
        ));

        return back();
    }
}
