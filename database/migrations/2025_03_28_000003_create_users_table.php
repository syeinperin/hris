<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Basic user info
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            // Link to roles table via role_id (defaulting to your Employee role)
            $table->unsignedBigInteger('role_id')->default(5);
            $table->foreign('role_id')
                  ->references('id')->on('roles')
                  ->onDelete('cascade');

            // Optional profile picture
            $table->string('profile_picture')->nullable();

            // Status and activity
            $table->enum('status', ['pending','active','inactive'])
                  ->default('pending');
            $table->timestamp('last_login')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
