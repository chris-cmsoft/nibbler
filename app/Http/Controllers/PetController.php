<?php

namespace App\Http\Controllers;

use App\Events\PetCareUpdated;
use App\Events\PetReturned;
use App\Jobs\FeedPet;
use App\Models\Pet;
use App\Models\Team;
use App\Support\PetPayload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                ->map(fn (Pet $pet) => PetPayload::fromPet($pet)),
        ]);
    }

    /**
     * Feed the given pet.
     */
    public function feed(Request $request, Team $currentTeam, Pet $pet): RedirectResponse
    {
        $pet = $currentTeam->pets()
            ->with('animal')
            ->whereKey($pet->id)
            ->firstOrFail();

        Log::info('Pet manually fed.', [
            'pet_id' => $pet->id,
            'pet_name' => $pet->name,
            'team_id' => $currentTeam->id,
            'user_id' => $request->user()->id,
            'animal_id' => $pet->animal_id,
            'animal_name' => $pet->animal->name,
            'calorie_level' => $pet->calorie_level,
            'daily_calories' => $pet->animal->calories_per_day,
            'requested_calories' => 100,
        ]);

        FeedPet::dispatch($pet->id);

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
     * Return the given pet from the team.
     */
    public function destroy(Request $request, Team $currentTeam, Pet $pet): RedirectResponse
    {
        $pet = $currentTeam->pets()
            ->whereKey($pet->id)
            ->firstOrFail();

        $petId = $pet->id;
        $petName = $pet->name;

        $pet->delete();

        broadcast(new PetReturned(
            teamId: $currentTeam->id,
            petId: $petId,
            actorName: $request->user()->name,
            petName: $petName,
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
                ->with('animal')
                ->whereKey($pet->id)
                ->lockForUpdate()
                ->firstOrFail();

            $maximumCareLevel = $column === 'attention_level'
                ? $pet->animal->attention_points
                : 100;

            if ($column === 'attention_level' && $pet->attention_level >= $maximumCareLevel) {
                Log::warning('Pet is getting annoyed from too much petting.', [
                    'pet_id' => $pet->id,
                    'pet_name' => $pet->name,
                    'team_id' => $team->id,
                    'attention_level' => $pet->attention_level,
                    'attention_limit' => $maximumCareLevel,
                    'requested_attention_points' => 10,
                ]);
            }

            $pet->update([
                $column => min($maximumCareLevel, $pet->{$column} + 10),
            ]);

            return $pet;
        });
    }
}
