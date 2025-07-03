<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // store as DATE, unique so you can’t double‐add the same day
            $table->date('date')->unique();
            // lowercase enum to match our validation
            $table->enum('type', ['regular','special']);
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('holidays');
    }
};
