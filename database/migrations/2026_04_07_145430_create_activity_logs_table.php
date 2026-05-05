<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // who performed the action
            $table->string('action');                          // e.g. 'verified_submission', 'rejected_submission'
            $table->string('subject_type')->nullable();        // e.g. 'App\Models\Submission'
            $table->unsignedBigInteger('subject_id')->nullable(); // ID of the related record
            $table->text('description')->nullable();           // human-readable description
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
