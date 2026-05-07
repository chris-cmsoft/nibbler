<?php

use App\Http\Controllers\AdoptionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\PetRedirectController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('pets', PetRedirectController::class)->middleware('auth')->name('pets.redirect');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('pets', [PetController::class, 'index'])->name('pets.index');
        Route::post('pets/{pet}/feed', [PetController::class, 'feed'])->name('pets.feed');
        Route::post('pets/{pet}/pet', [PetController::class, 'pet'])->name('pets.pet');
        Route::delete('pets/{pet}', [PetController::class, 'destroy'])->name('pets.destroy');

        Route::get('adoptions', [AdoptionController::class, 'index'])->name('adoptions.index');
        Route::post('adoptions/{animal}', [AdoptionController::class, 'store'])->name('adoptions.store');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
});

require __DIR__.'/settings.php';
