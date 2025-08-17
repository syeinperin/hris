<?php
// database/migrations/2025_08_03_000000_create_approvals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApprovalsTable extends Migration
{
    public function up()
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            // polymorphic: can approve Users, LeaveRequests, etc.
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            // who actually approved/rejected
            $table->foreignId('approver_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            // who requested this approval
            $table->foreignId('requested_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->enum('status', ['pending','approved','rejected'])
                  ->default('pending');
            $table->timestamps();

            $table->index(['approvable_type','approvable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approvals');
    }
}
