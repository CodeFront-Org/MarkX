<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'roles')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('roles')->nullable()->after('role');
            });

            // Backfill roles column for existing users
            $users = DB::table('users')->get();
            foreach ($users as $user) {
                $roles = [];
                if (!empty($user->role)) {
                    $roles[] = $user->role;
                } else {
                    $roles[] = 'rfq_processor';
                }
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['roles' => json_encode($roles)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'roles')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('roles');
            });
        }
    }
};
