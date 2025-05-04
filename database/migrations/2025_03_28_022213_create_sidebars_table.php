<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSidebarsTable extends Migration
{
    public function up()
    {
        Schema::create('sidebars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('sidebars')
                  ->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->json('roles')->nullable()
                  ->comment('List of role slugs allowed to view');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sidebars');
    }
}
