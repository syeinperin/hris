<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class CreatePermissionTables extends Migration
{
    public function up(): void
    {
        // disable FK checks so we can drop even if referenced
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permission_roles');  // <-- here
        Schema::dropIfExists('permissions');

        Schema::enableForeignKeyConstraints();

        // 1) permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        // 2) permission_roles (Spatie’s roles, renamed)
        Schema::create('permission_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        // 3) model_has_permissions pivot
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(
                ['model_id','model_type'],
                'model_has_permissions_model_id_model_type_index'
            );

            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade');

            $table->primary(
                ['permission_id','model_id','model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        // 4) model_has_roles pivot
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(
                ['model_id','model_type'],
                'model_has_roles_model_id_model_type_index'
            );

            $table->foreign('role_id')
                  ->references('id')
                  ->on('permission_roles')   // <-- here
                  ->onDelete('cascade');

            $table->primary(
                ['role_id','model_id','model_type'],
                'model_has_roles_role_model_type_primary'
            );
        });

        // 5) role_has_permissions pivot
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->onDelete('cascade');

            $table->foreign('role_id')
                  ->references('id')
                  ->on('permission_roles')   // <-- here
                  ->onDelete('cascade');

            $table->primary(
                ['permission_id','role_id'],
                'role_has_permissions_permission_id_role_id_primary'
            );
        });

        // forget Spatie’s cached permissions
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permission_roles');  // <-- here
        Schema::dropIfExists('permissions');

        Schema::enableForeignKeyConstraints();
    }
}
