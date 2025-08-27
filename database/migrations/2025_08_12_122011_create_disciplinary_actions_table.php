<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // If the table doesn't exist yet, create it with the full schema.
        if (!Schema::hasTable('disciplinary_actions')) {
            Schema::create('disciplinary_actions', function (Blueprint $table) {
                $table->id();

                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->foreignId('issued_by')->constrained('users')->cascadeOnDelete();

                $table->enum('action_type', ['violation', 'suspension'])->index();
                $table->string('category')->nullable();
                $table->enum('severity', ['minor', 'major', 'critical'])->default('minor');
                $table->unsignedTinyInteger('points')->nullable();

                $table->text('reason');
                $table->date('start_date')->nullable(); // used for suspension
                $table->date('end_date')->nullable();   // used for suspension

                $table->enum('status', ['active', 'resolved'])->default('active');
                $table->text('notes')->nullable();

                $table->timestamps();

                $table->index(['employee_id', 'status'], 'da_emp_status_idx');
            });

            return;
        }

        // If the table already exists (likely your case), patch any missing columns.
        Schema::table('disciplinary_actions', function (Blueprint $table) {
            if (!Schema::hasColumn('disciplinary_actions', 'action_type')) {
                $table->enum('action_type', ['violation', 'suspension'])
                      ->after('issued_by')
                      ->index();
            }
            if (!Schema::hasColumn('disciplinary_actions', 'category')) {
                $table->string('category')->nullable()->after('action_type');
            }
            if (!Schema::hasColumn('disciplinary_actions', 'severity')) {
                $table->enum('severity', ['minor', 'major', 'critical'])
                      ->default('minor')
                      ->after('category');
            }
            if (!Schema::hasColumn('disciplinary_actions', 'points')) {
                $table->unsignedTinyInteger('points')->nullable()->after('severity');
            }
            if (!Schema::hasColumn('disciplinary_actions', 'reason')) {
                $table->text('reason')->after('points');
            }
            if (!Schema::hasColumn('disciplinary_actions', 'start_date')) {
                $table->date('start_date')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('disciplinary_actions', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('disciplinary_actions', 'status')) {
                $table->enum('status', ['active', 'resolved'])
                      ->default('active')
                      ->after('end_date');
            }
            if (!Schema::hasColumn('disciplinary_actions', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });

        // (Optional) ensure the composite index exists if the table was old.
        // Creating duplicate indexes throws an error, so skip unless you know it's missing.
        // You can add it manually in MySQL if needed:
        // CREATE INDEX da_emp_status_idx ON disciplinary_actions (employee_id, status);
    }

    public function down(): void
    {
        // Standard rollback: drop the table.
        Schema::dropIfExists('disciplinary_actions');
    }
};
