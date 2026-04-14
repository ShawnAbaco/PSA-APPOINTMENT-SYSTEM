<?php
// database/migrations/2026_04_14_000005_create_services_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // reg, correction, ephilid, trn
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();
            $table->integer('estimated_duration_minutes')->default(15);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        
        // Insert default services
        DB::table('services')->insert([
            [
                'code' => 'reg',
                'name' => 'National ID Registration',
                'description' => 'First-time registration for Philippine National ID',
                'requirements' => 'PSA Birth Certificate + 1 government-issued ID',
                'estimated_duration_minutes' => 20,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'correction',
                'name' => 'Correction/Updating of Demographic Information',
                'description' => 'Update or correct personal information in PhilSys',
                'requirements' => 'Supporting documents depending on the field to update',
                'estimated_duration_minutes' => 15,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ephilid',
                'name' => 'Issuance of National ID Paper Form (ePhilID)',
                'description' => 'Printing of ePhilID while waiting for physical card',
                'requirements' => 'Transaction slip or reference number',
                'estimated_duration_minutes' => 10,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'trn',
                'name' => 'Retrieval of TRN / Other Concern',
                'description' => 'Retrieve lost Transaction Reference Number',
                'requirements' => 'Valid ID for verification',
                'estimated_duration_minutes' => 10,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};