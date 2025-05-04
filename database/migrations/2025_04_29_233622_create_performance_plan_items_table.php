<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_plan_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('weight',5,2)
                  ->comment('Percentage weight');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_plan_items');
    }
};
