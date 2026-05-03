<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table): void {
            $table->dateTime('registration_open_at')->nullable()->change();
            $table->dateTime('registration_close_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table): void {
            $table->dateTime('registration_open_at')->nullable(false)->change();
            $table->dateTime('registration_close_at')->nullable(false)->change();
        });
    }
};
