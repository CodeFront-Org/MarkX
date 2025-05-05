<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->bigInteger('current_value');
            $table->timestamps();
        });

        // Initialize invoice sequence
        DB::table('sequences')->insert([
            'name' => 'invoice_number',
            'current_value' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};