<?php
// database/migrations/2026_04_20_000010_create_working_days_defaults_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_days_defaults', function (Blueprint $table) {
            $table->id();
            $table->integer('day_of_week'); // 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat, 7=Sun
            $table->string('day_name');
            $table->boolean('is_working')->default(true);
            $table->timestamps();
        });
        
        // Insert default working days
        DB::table('working_days_defaults')->insert([
            ['day_of_week' => 1, 'day_name' => 'Monday', 'is_working' => true],
            ['day_of_week' => 2, 'day_name' => 'Tuesday', 'is_working' => true],
            ['day_of_week' => 3, 'day_name' => 'Wednesday', 'is_working' => true],
            ['day_of_week' => 4, 'day_name' => 'Thursday', 'is_working' => true],
            ['day_of_week' => 5, 'day_name' => 'Friday', 'is_working' => true],
            ['day_of_week' => 6, 'day_name' => 'Saturday', 'is_working' => false],
            ['day_of_week' => 7, 'day_name' => 'Sunday', 'is_working' => false],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('working_days_defaults');
    }
};