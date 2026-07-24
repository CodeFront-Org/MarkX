<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    protected $signature = 'user:make-superadmin {email : The email address of the user to promote}';
    protected $description = 'Promote an existing user to the superadmin role (full system access)';

    public function handle()
    {
        $email = $this->argument('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        if ($user->isSuperAdmin()) {
            $this->info("{$user->name} ({$email}) is already a super admin.");
            return self::SUCCESS;
        }

        $previousRole = $user->role;
        $user->update(['role' => 'superadmin']);

        $this->info("Promoted {$user->name} ({$email}) from '{$previousRole}' to 'superadmin'.");

        return self::SUCCESS;
    }
}
