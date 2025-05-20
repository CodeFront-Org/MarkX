<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */   
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('reference')->after('id');
        });

        // Update existing quotes with a reference number
        DB::table('quotes')->orderBy('id')->each(function ($quote) {
            DB::table('quotes')
                ->where('id', $quote->id)
                ->update(['reference' => 'Q' . str_pad($quote->id, 6, '0', STR_PAD_LEFT)]);
        });

        // Add unique constraint after data is populated
        Schema::table('quotes', function (Blueprint $table) {
            $table->unique('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
};
