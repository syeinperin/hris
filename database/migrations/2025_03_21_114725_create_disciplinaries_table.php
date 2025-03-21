<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disciplinaries', function (Blueprint $table) { 
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); 
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['Pending', 'Warning', 'Suspended']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinaries'); // ✅ Table name fixed
    }
};
