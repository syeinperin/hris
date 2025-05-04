<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();

            // link each payslip back to its user:
            $table->foreignId('user_id')
                  ->constrained()        // references users.id
                  ->cascadeOnDelete();

            $table->date('period_start');
            $table->date('period_end');

            // breakdown fields:
            $table->decimal('worked_hours', 8, 2)->default(0);
            $table->decimal('ot_hours',      8, 2)->default(0);
            $table->decimal('ot_pay',       12, 2)->default(0);
            $table->decimal('deductions',   12, 2)->default(0);

            // stored gross/net:
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('net_amount',   12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
