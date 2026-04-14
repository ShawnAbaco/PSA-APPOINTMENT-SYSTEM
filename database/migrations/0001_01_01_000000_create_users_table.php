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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Employee/Staff ID (optional, not primary)
            $table->string('employee_id')->nullable()->unique();
            
            // Login credentials
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            
            // Personal information
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('name')->virtualAs('CONCAT(first_name, " ", last_name)')->nullable();
            
            // Contact information
            $table->string('contact_number')->nullable();
            $table->string('alternate_contact')->nullable();
            
            // Role and permissions
            $table->enum('role', ['admin', 'staff', 'user'])->default('user');
            $table->boolean('is_active')->default(true);
            
            // Staff specific fields
            $table->string('position')->nullable();
            $table->string('department')->nullable();
            
            // Profile and metadata
            $table->string('profile_photo')->nullable();
            $table->json('permissions')->nullable();
            
            // Tracking and audit
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            // Two-factor authentication
            $table->string('two_factor_secret')->nullable();
            $table->string('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['role', 'is_active']);
            $table->index('employee_id');
            $table->index('last_login_at');
            $table->index('username');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};