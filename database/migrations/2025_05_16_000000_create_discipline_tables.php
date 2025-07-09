<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('infraction_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $t->foreignId('reported_by')->constrained('users')->cascadeOnDelete();
            $t->string('location');
            $t->text('description');
            $t->date('incident_date');
            $t->time('incident_time')->nullable();
            $t->boolean('similar_before')->default(false);
            $t->integer('similar_count')->nullable();
            $t->boolean('confidential')->default(false);
            $t->boolean('will_testify')->default(false);
            $t->timestamps();
        });

        // ← Rename this to the plural so Eloquent’s default table name matches:
        Schema::create('infraction_investigators', function (Blueprint $t) {
            $t->id();
            $t->foreignId('infraction_report_id')->constrained('infraction_reports')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->timestamps();
        });

        Schema::create('action_types', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();
            $t->string('description');
            $t->string('severity_level');
            $t->string('outcome');
            $t->string('leave_policy')->nullable();
            $t->integer('leave_days')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });

        Schema::create('disciplinary_actions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('infraction_report_id')->constrained('infraction_reports')->cascadeOnDelete();
            $t->foreignId('action_type_id')->constrained('action_types')->restrictOnDelete();
            $t->date('action_date');
            $t->boolean('link_payroll')->default(false);
            $t->boolean('link_hiring')->default(false);
            $t->boolean('terminate_employee')->default(false);
            $t->json('approvals')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_actions');
        Schema::dropIfExists('action_types');
        // ← And make sure you drop the plural, too:
        Schema::dropIfExists('infraction_investigators');
        Schema::dropIfExists('infraction_reports');
    }
};
