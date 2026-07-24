<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Audit trail of each approval/rejection action taken on a quote while it
     * walks the approver chain. Also used to derive the quote's current step
     * (the first chain approver with no 'approved' record for the quote).
     */
    public function up(): void
    {
        Schema::create('quote_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['approved', 'rejected']);
            $table->boolean('is_override')->default(false);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['quote_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_approvals');
    }
};
