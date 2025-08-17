<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();

            $table->enum('action_type', ['violation','suspension'])->index();
            $table->string('category')->nullable();
            $table->enum('severity', ['minor','major','critical'])->default('minor');
            $table->unsignedTinyInteger('points')->nullable();

            $table->text('reason');
            $table->date('start_date')->nullable(); // used for suspension
            $table->date('end_date')->nullable();   // used for suspension

            $table->enum('status', ['active','resolved'])->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['employee_id','status'], 'da_emp_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_actions');
    }
};
