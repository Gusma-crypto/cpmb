<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_card_print_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_card_id')->constrained('exam_cards')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('printed_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['exam_card_id', 'printed_at'], 'exam_card_print_log_card_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_card_print_logs');
    }
};
