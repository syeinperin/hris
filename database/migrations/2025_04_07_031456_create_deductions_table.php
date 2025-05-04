<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);

            // NEW: replace old `date` column
            $table->date('effective_from')
                  ->comment('First day deduction applies');
            $table->date('effective_until')
                  ->nullable()
                  ->comment('Last day deduction applies (or NULL for ongoing)');

            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
