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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->string('registration_number')->unique();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'revision_required', 'verified', 'rejected', 'exam_ready'])->default('draft');
            $table->tinyInteger('wave')->unsigned()->nullable();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->enum('payment_status', ['unpaid', 'pending', 'paid'])->nullable();
            $table->string('payment_ref')->nullable();
            $table->text('revision_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['academic_year_id', 'program_id']);
            $table->index(['status', 'payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
