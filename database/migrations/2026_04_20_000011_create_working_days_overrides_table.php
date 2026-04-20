<?php
// database/migrations/2026_04_20_000011_create_working_days_overrides_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_days_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->boolean('is_working')->default(true);
            $table->string('reason')->nullable(); // Holiday, Special Event, etc.
            $table->timestamps();
            
            $table->unique('date');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_days_overrides');
    }
};