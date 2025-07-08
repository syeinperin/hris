<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();

            // Link back to users table
            $table->foreignId('user_id')
                  ->constrained()       // references users.id
                  ->cascadeOnDelete();

            // Always assign to an employee
            $table->foreignId('employee_id')
                  ->constrained()       // references employees.id
                  ->cascadeOnDelete();

            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason')->nullable();

            $table->enum('status', ['pending','approved','rejected'])
                  ->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop FK explicitly before table
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('leave_requests');
    }
};
