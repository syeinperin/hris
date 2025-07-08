<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_plans', function (Blueprint $table) {
            $table->id();          // Must be unsigned BIGINT
            $table->string('name');
            $table->integer('months');
            $table->decimal('rate',5,2)->comment('Interest rate %');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_plans');
    }
};
