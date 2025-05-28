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
        // For SQLite, we need to recreate the table to change an enum constraint
        
        // First, let's create a temporary table with the right structure
        Schema::create('quotes_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('marketer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // Change enum to string
            $table->date('valid_until');
            $table->string('rejection_reason')->nullable();
            $table->text('rejection_details')->nullable();
            $table->string('reference')->nullable();
            $table->boolean('has_rfq')->default(false);
            $table->integer('rfq_files_count')->default(0);
            $table->string('contact_person')->nullable();
            $table->integer('total_rfq_items')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Now move data if any exists
        $quotes = DB::table('quotes')->get();
        foreach ($quotes as $quote) {
            $data = (array)$quote;
            DB::table('quotes_new')->insert($data);
        }
        
        // Drop the original table
        Schema::dropIfExists('quotes');
        
        // Rename the new table to the original name
        Schema::rename('quotes_new', 'quotes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive operation, so we can't really reverse it
        // But we'll provide a basic structure for completeness
        Schema::table('quotes', function (Blueprint $table) {
            // We'd need to recreate the enum, but that would require similar steps
            // as the up method, which is complex for a down method
        });
    }
}; 