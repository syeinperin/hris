<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //
        // 1) performance_forms
        //
        Schema::create('performance_forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        //
        // 2) performance_criteria
        //
        Schema::create('performance_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')
                  ->constrained('performance_forms')
                  ->onDelete('cascade');
            $table->text('text')->comment('Criterion description');
            $table->tinyInteger('default_score')->default(0);
            $table->timestamps();
        });

        //
        // 3) performance_form_assignments
        //
        Schema::create('performance_form_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')
                  ->constrained('performance_forms')
                  ->onDelete('cascade');
            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->onDelete('cascade');
            $table->foreignId('evaluator_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // NEW evaluation window columns
            $table->date('starts_at')->nullable();
            $table->date('ends_at')  ->nullable();

            $table->timestamps();
        });

        //
        // 4) performance_evaluations
        //
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')
                  ->constrained('performance_forms')
                  ->onDelete('cascade');
            $table->foreignId('employee_id')
                  ->constrained('employees')
                  ->onDelete('cascade');
            $table->foreignId('evaluator_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->date('evaluated_on');
            $table->integer('total_score')->default(0);
            $table->text('comments')
                  ->nullable()
                  ->comment('Overall evaluator remarks');
            $table->timestamps();
        });

        //
        // 5) performance_evaluation_details
        //
        Schema::create('performance_evaluation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')
                  ->constrained('performance_evaluations')
                  ->onDelete('cascade');
            $table->foreignId('criterion_id')
                  ->constrained('performance_criteria')
                  ->onDelete('cascade');

            $table->enum('rating', [
                'Needs Improvement',
                'Unsatisfactory',
                'Fair',
                'Satisfactory',
                'Good',
                'Excellent',
            ])->comment('Needs Improvement, Unsatisfactory, Fair, Satisfactory, Good, Excellent');

            $table->text('comments')
                  ->nullable()
                  ->comment('Per-criterion remarks');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_evaluation_details');
        Schema::dropIfExists('performance_evaluations');
        Schema::dropIfExists('performance_form_assignments');
        Schema::dropIfExists('performance_criteria');
        Schema::dropIfExists('performance_forms');
    }
};
