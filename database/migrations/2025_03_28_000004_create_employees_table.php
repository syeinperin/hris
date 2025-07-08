<?php

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

            // Personal
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

            // Start/End dates
            $table->date('employment_start_date');
            $table->date('employment_end_date');

            // Addresses
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();

            // Family & History
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

            // *NEW* track when employee last updated their own profile
            $table->timestamp('profile_updated_at')->nullable();

            // Benefits
            $table->string('gsis_id_no')->nullable();
            $table->string('pagibig_id_no')->nullable();
            $table->string('philhealth_tin_id_no')->nullable();
            $table->string('sss_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('agency_employee_no')->nullable();

            // Soft deletes + timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}