<?php
// database/migrations/2026_04_14_000008_create_working_days_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_days_defaults', function (Blueprint $table) {
            $table->id();
            $table->integer('day_of_week');
            $table->string('day_name');
            $table->boolean('is_working')->default(true);
            $table->timestamps();
        });

        Schema::create('working_days_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->boolean('is_working')->default(true);
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_days_overrides');
        Schema::dropIfExists('working_days_defaults');
    }
};