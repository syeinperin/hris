<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('reference_no')->unique();
            $table->foreignId('loan_type_id')
                  ->constrained('loan_types')
                  ->onDelete('restrict');
            $table->foreignId('plan_id')
                  ->constrained('loan_plans')
                  ->onDelete('restrict');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->integer('term_months');
            $table->decimal('total_payable', 15, 2);
            $table->decimal('monthly_amount', 15, 2);
            $table->date('next_payment_date');
            $table->enum('status', ['active','paid','defaulted'])->default('active');
            $table->dateTime('released_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
