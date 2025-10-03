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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('device_pin')->nullable()->after('is_manual');
            $table->timestamp('recorded_at')->nullable()->after('device_pin');
            $table->tinyInteger('status')->nullable()->after('recorded_at');
            $table->string('device_id')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['device_pin', 'recorded_at', 'status', 'device_id']);
        });
    }
};
