<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cbt_attempt_id')->nullable()->constrained('cbt_attempts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->json('payload')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['cbt_attempt_id', 'event'], 'cbt_audit_attempt_event_idx');
            $table->index(['user_id', 'event'], 'cbt_audit_user_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_audit_logs');
    }
};
