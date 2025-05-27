<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateUserRoleConstraint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop existing triggers
        DB::statement("DROP TRIGGER IF EXISTS check_user_role");
        DB::statement("DROP TRIGGER IF EXISTS check_user_role_update");

        // Create new triggers with updated role list
        DB::statement("CREATE TRIGGER check_user_role
            BEFORE INSERT ON users
            FOR EACH ROW
            BEGIN
                IF NEW.role NOT IN ('manager', 'marketer', 'finance') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Invalid role';
                END IF;
            END;");

        DB::statement("CREATE TRIGGER check_user_role_update
            BEFORE UPDATE ON users
            FOR EACH ROW
            BEGIN
                IF NEW.role NOT IN ('manager', 'marketer', 'finance') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Invalid role';
                END IF;
            END;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the triggers
        DB::statement("DROP TRIGGER IF EXISTS check_user_role");
        DB::statement("DROP TRIGGER IF EXISTS check_user_role_update");

        // Recreate original triggers
        DB::statement("CREATE TRIGGER check_user_role
            BEFORE INSERT ON users
            FOR EACH ROW
            BEGIN
                IF NEW.role NOT IN ('manager', 'marketer') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Invalid role';
                END IF;
            END;");

        DB::statement("CREATE TRIGGER check_user_role_update
            BEFORE UPDATE ON users
            FOR EACH ROW
            BEGIN
                IF NEW.role NOT IN ('manager', 'marketer') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Invalid role';
                END IF;
            END;");
    }
} 