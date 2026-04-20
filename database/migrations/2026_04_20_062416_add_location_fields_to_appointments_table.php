<?php
// database/migrations/2024_01_01_000001_add_location_fields_to_appointments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationFieldsToAppointmentsTable extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add location columns
            $table->decimal('user_lat', 10, 8)->nullable()->after('status');
            $table->decimal('user_lng', 11, 8)->nullable()->after('user_lat');
            $table->string('user_city', 100)->nullable()->after('user_lng');
            $table->text('user_address')->nullable()->after('user_city');
            $table->string('user_zipcode', 20)->nullable()->after('user_address');
            
            // Add indexes for better performance
            $table->index('user_city');
            $table->index(['user_lat', 'user_lng']);
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['user_lat', 'user_lng', 'user_city', 'user_address', 'user_zipcode']);
            $table->dropIndex(['user_city']);
            $table->dropIndex(['user_lat', 'user_lng']);
        });
    }
}