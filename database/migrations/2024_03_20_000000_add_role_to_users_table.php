<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRoleToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('marketer');
            }
        });

        // Add check constraint using trigger for SQLite
        DB::statement("CREATE TRIGGER IF NOT EXISTS check_user_role
            BEFORE INSERT ON users
            BEGIN
                SELECT CASE
                    WHEN NEW.role NOT IN ('manager', 'marketer', 'finance')
                    THEN RAISE (ABORT, 'Invalid role')
                END;
            END;");

        DB::statement("CREATE TRIGGER IF NOT EXISTS check_user_role_update
            BEFORE UPDATE ON users
            BEGIN
                SELECT CASE
                    WHEN NEW.role NOT IN ('manager', 'marketer', 'finance')
                    THEN RAISE (ABORT, 'Invalid role')
                END;
            END;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the triggers first
        DB::statement("DROP TRIGGER IF EXISTS check_user_role");
        DB::statement("DROP TRIGGER IF EXISTS check_user_role_update");

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
} 