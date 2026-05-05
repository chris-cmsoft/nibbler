<?php

use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('pets.{pet}', function (User $user, Pet $pet): bool {
    return $user->teams()->whereKey($pet->team_id)->exists();
});
