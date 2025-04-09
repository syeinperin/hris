<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            
            // Role reference: assumes you have a roles table with appropriate IDs.
            $table->unsignedBigInteger('role_id'); 
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            
            $table->string('email')->unique();
            $table->string('password');

            // Using an ENUM for status; values: active, inactive (default: active)
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Add a nullable timestamp for last login
            $table->timestamp('last_login')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }    

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
