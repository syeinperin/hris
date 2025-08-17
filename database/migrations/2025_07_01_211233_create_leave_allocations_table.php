<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveAllocationsTable extends Migration
{
    public function up()
    {
        Schema::create('leave_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->integer('days_allocated')->default(0);
            $table->integer('days_used')->default(0);
            $table->timestamps();

            $table->unique(['leave_type_id', 'employee_id', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_allocations');
    }
}
