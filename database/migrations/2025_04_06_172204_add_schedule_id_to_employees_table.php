<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleIdToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add a nullable schedule_id column after designation_id
            $table->unsignedBigInteger('schedule_id')->nullable()->after('designation_id');

            // Add a foreign key constraint to the schedules table
            $table->foreign('schedule_id')
                  ->references('id')->on('schedules')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the foreign key and the column when rolling back
            $table->dropForeign(['schedule_id']);
            $table->dropColumn('schedule_id');
        });
    }
}
