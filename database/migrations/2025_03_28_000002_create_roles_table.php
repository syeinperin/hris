<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            // Role name (e.g. "admin", "employee")
            $table->string('name');

            // Spatie requires a guard_name column; default to "web"
            $table->string('guard_name')->default('web');

            $table->timestamps();

            // Unique together so you can scope same role names under different guards
            $table->unique(['name','guard_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
