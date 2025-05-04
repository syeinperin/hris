<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();                                  // unsignedBigInteger PK
            $table->foreignId('performance_plan_id')
                  ->constrained('performance_plans')
                  ->cascadeOnDelete();
            $table->foreignId('employee_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('evaluator_id')
                  ->constrained('users');
            $table->date('evaluation_date');
            $table->tinyInteger('rating');
            $table->text('comments')->nullable();
            $table->enum('status',['pending','completed'])
                  ->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};
