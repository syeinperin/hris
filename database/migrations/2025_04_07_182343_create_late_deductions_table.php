<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('late_deductions', function (Blueprint $table) {
            $table->id();

            // min minutes late (inclusive)
            $table->unsignedInteger('mins_min')
                  ->comment('Minimum minutes late for this bracket');

            // max minutes late (inclusive)
            $table->unsignedInteger('mins_max')
                  ->comment('Maximum minutes late for this bracket');

            // multiplier of hourly rate
            $table->decimal('multiplier', 5, 2)
                  ->comment('Portion of 1 hour to deduct from hourly rate');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('late_deductions');
    }
};
