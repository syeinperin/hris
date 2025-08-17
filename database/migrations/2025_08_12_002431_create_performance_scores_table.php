<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_id')->constrained('performance_evaluations')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('performance_items')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('weight_cache');
            $table->decimal('weighted_score', 6, 3)->default(0);
            $table->timestamps();
            $table->unique(['evaluation_id','item_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('performance_scores');
    }
};
