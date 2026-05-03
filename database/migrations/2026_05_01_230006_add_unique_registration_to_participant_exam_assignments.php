<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_exam_assignments', function (Blueprint $table) {
            $table->unique('registration_id', 'participant_exam_registration_unique');
        });
    }

    public function down(): void
    {
        Schema::table('participant_exam_assignments', function (Blueprint $table) {
            $table->dropUnique('participant_exam_registration_unique');
        });
    }
};
