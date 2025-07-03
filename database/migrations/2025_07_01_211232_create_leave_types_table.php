<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateLeaveTypesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();

            // machine‐friendly key (must be unique & non-null)
            $table->string('key')->unique();

            // human‐readable name
            $table->string('name')->unique();

            // default entitlement (in days)
            $table->integer('default_days')->default(0);

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        // ── Seed the three statutory leave types ─────────────────
        $now = Carbon::now();
        DB::table('leave_types')->insert([
            [
                'key'          => 'service',
                'name'         => 'Service Incentive Leave',
                'default_days' => 5,
                'description'  => 'Five days of annual service incentive leave.',
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'key'          => 'maternity',
                'name'         => 'Maternity Leave',
                'default_days' => 105,
                'description'  => 'One hundred five days maternity leave.',
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'key'          => 'paternity',
                'name'         => 'Paternity Leave',
                'default_days' => 7,
                'description'  => 'Seven days paternity leave.',
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
}
