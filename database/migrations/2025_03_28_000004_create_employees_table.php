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
            $table->string('name');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('previous_company')->nullable();
            $table->string('job_title')->nullable();
            $table->float('years_experience')->nullable();
            $table->string('nationality')->nullable();

            // Foreign Keys to department and designation
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('designation_id')->constrained()->onDelete('cascade');

            // New schedule_id column with foreign key to schedules table
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->foreign('schedule_id')
                  ->references('id')->on('schedules')
                  ->onDelete('set null');

            // Other fields
            $table->string('fingerprint_id')->nullable()->unique();
            $table->string('profile_picture')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
