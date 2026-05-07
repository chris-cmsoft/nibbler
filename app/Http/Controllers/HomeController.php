<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Support\AnimalPayload;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Welcome', [
            'animals' => Animal::query()
                ->orderBy('name')
                ->get()
                ->map(fn (Animal $animal) => AnimalPayload::fromAnimal($animal)),
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
