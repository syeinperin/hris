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
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // You can keep the ID if you like, but it's not strictly needed.
            // $table->id();

            // Required columns for Laravel password resets:
            $table->string('email')->index();
            $table->string('token');
            // Typically, only one timestamp is used in the default approach:
            $table->timestamp('created_at')->nullable();

            // If you prefer the usual timestamps (created_at, updated_at):
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
