<?php
// database/migrations/XXXX_XX_XX_create_employees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique()->nullable();
            $table->string('name');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            // Core personal
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();

            // Status & Employment
            $table->enum('status', ['pending','active','inactive'])->default('active');
            $table->enum('employment_type', [
                'regular','casual','project','seasonal','fixed-term','probationary'
            ]);
            // ← made nullable so your nullable|date validation can pass
            $table->date('employment_start_date')->nullable();
            $table->date('employment_end_date');

            // **Legacy** single‐line address
            $table->text('current_address')->nullable();

            // New split address fields
            $table->string('current_street_address')->nullable();
            $table->string('current_city')->nullable();
            $table->string('current_province')->nullable();
            $table->string('current_postal_code')->nullable();

            // Permanent address (optional)
            $table->string('permanent_address')->nullable();

            // Family & history
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('previous_company')->nullable();
            $table->string('job_title')->nullable();
            $table->float('years_experience')->nullable();
            $table->string('nationality')->nullable();

            // Relations
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('designation_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->foreign('schedule_id')
                  ->references('id')->on('schedules')
                  ->onDelete('set null');

            // Other
            $table->string('fingerprint_id')->nullable()->unique();
            $table->string('profile_picture')->nullable();
            $table->timestamp('profile_updated_at')->nullable();

            // Benefits
            $table->string('gsis_id_no')->nullable();
            $table->string('pagibig_id_no')->nullable();
            $table->string('philhealth_tin_id_no')->nullable();
            $table->string('sss_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('agency_employee_no')->nullable();

            // ── Bio-Data: PERSONAL ────────────────────────────────────────────
            $table->string('position_desired')->nullable();
            $table->date('application_date')->nullable();
            $table->string('city_address')->nullable();
            $table->string('provincial_address')->nullable();
            $table->string('telephone')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('birth_place')->nullable();
            $table->enum('civil_status', ['single','married','widowed','separated','other'])->nullable();
            $table->string('citizenship')->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->string('religion')->nullable();
            $table->string('spouse')->nullable();
            $table->string('occupation')->nullable();
            $table->string('name_of_children')->nullable();
            $table->date('children_birth_date')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->text('languages_spoken')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_address')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // ── Bio-Data: EDUCATION ──────────────────────────────────────────
            $table->string('elementary_school')->nullable();
            $table->year('elementary_year_graduated')->nullable();
            $table->string('high_school')->nullable();
            $table->year('high_school_year_graduated')->nullable();
            $table->string('college')->nullable();
            $table->year('college_year_graduated')->nullable();
            $table->string('degree_received')->nullable();
            $table->text('special_skills')->nullable();

            // ── Bio-Data: EMPLOYMENT RECORD ─────────────────────────────────
            $table->string('emp1_company')->nullable();
            $table->string('emp1_position')->nullable();
            $table->date('emp1_from')->nullable();
            $table->date('emp1_to')->nullable();
            $table->string('emp2_company')->nullable();
            $table->string('emp2_position')->nullable();
            $table->date('emp2_from')->nullable();
            $table->date('emp2_to')->nullable();

            // ── Bio-Data: CHARACTER REFERENCES ───────────────────────────────
            $table->string('char1_name')->nullable();
            $table->string('char1_position')->nullable();
            $table->string('char1_company')->nullable();
            $table->string('char1_contact')->nullable();
            $table->string('char2_name')->nullable();
            $table->string('char2_position')->nullable();
            $table->string('char2_company')->nullable();
            $table->string('char2_contact')->nullable();

            // ── Bio-Data: CERTIFICATES ───────────────────────────────────────
            $table->string('res_cert_no')->nullable();
            $table->string('res_cert_issued_at')->nullable();
            $table->date('res_cert_issued_on')->nullable();
            $table->string('nbi_no')->nullable();
            $table->string('passport_no')->nullable();

            // Soft deletes & timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
