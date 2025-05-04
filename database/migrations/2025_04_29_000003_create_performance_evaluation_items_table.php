<?php
// database/migrations/2025_04_20_000000_create_performance_evaluation_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_evaluation_items', function (Blueprint $table) {
            $table->id();
            
            // this must match performance_evaluations.id (unsignedBigInteger)
            $table->foreignId('performance_evaluation_id')
                  ->constrained('performance_evaluations')
                  ->cascadeOnDelete();

            // if you have more fields on each item:
            $table->string('metric');    // e.g. KPI name or competency
            $table->integer('target');   // e.g. target score
            $table->integer('actual')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_evaluation_items');
    }
};
