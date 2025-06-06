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

            // Custom employee_code (e.g., "EMP12345")
            $table->string('employee_code')->unique()->nullable();

            // Basic name + link to user
            $table->string('name');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');

            // Contact / personal info
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();

            // Status (admin‐controlled)
            $table->enum('status', ['pending','active','inactive'])
                  ->default('active');

            // Employment type (must always be one of these; no default, not nullable)
            $table->enum('employment_type', [
                'regular',
                'casual',
                'project',
                'seasonal',
                'fixed-term',
                'probationary'
            ]);

            // Every employment type must have an end date
            $table->date('employment_end_date');

            // Newly added fields (all nullable)
            $table->string('sss_id')->nullable();
            $table->string('pagibig_id')->nullable();
            $table->string('philhealth_id')->nullable();

            // Addresses
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();

            // Family
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();

            // Work history
            $table->string('previous_company')->nullable();
            $table->string('job_title')->nullable();
            $table->float('years_experience')->nullable();
            $table->string('nationality')->nullable();

            // Organization links
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('designation_id')->constrained()->onDelete('cascade');

            // Schedule (nullable FK)
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->foreign('schedule_id')
                  ->references('id')->on('schedules')
                  ->onDelete('set null');

            // Other
            $table->string('fingerprint_id')->nullable()->unique();
            $table->string('profile_picture')->nullable();

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
