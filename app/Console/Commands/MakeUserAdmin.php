<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:make-admin {email : The email address of the user to promote}')]
#[Description('Promote a user to application administrator')]
class MakeUserAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email [{$email}].");

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->info("User [{$email}] is already an administrator.");

            return self::SUCCESS;
        }

        $user->forceFill([
            'is_admin' => true,
        ])->save();

        $this->info("User [{$email}] is now an administrator.");

        return self::SUCCESS;
    }
}
