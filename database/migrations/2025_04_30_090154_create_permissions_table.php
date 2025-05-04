<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) The permissions master table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // If you ever need per-guard permissions, keep this; otherwise you can remove:
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        // 2) The pivot your RolesTableSeeder is deleting/inserting into:
        Schema::create('role_has_permissions', function (Blueprint $table) {
            // assumes you already have a `roles` table
            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->cascadeOnDelete();

            $table->foreignId('permission_id')
                  ->constrained('permissions')
                  ->cascadeOnDelete();

            // composite primary key = no duplicates
            $table->primary(['role_id','permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
    }
};
