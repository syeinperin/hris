﻿<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['submitted','draft'])->default('submitted');
            $table->timestamps();
            $table->index(['employee_id','period_start','period_end'], 'eval_emp_period_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};
