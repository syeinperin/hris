<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRoleUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function inline_role_update_route_can_change_a_users_role()
    {
        // 1) Seed two roles: "admin" and "hr"
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $hrRole    = Role::factory()->create(['name' => 'hr']);

        // 2) Create an admin user (to act-as) and a target user
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $user  = User::factory()->create(['role_id' => $adminRole->id]);

        // 3) Act as the admin, hit the inline‐role‐update endpoint
        $this->actingAs($admin);
        $response = $this->json(
            'PUT',
            route('users.updateRole', $user),
            ['role' => 'hr']
        );

        // 4) Assert we got a 200 + correct JSON
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Role updated to Hr',
                     'role'    => 'hr',
                 ]);

        // 5) And finally the user record in the DB got updated
        $this->assertDatabaseHas('users', [
            'id'      => $user->id,
            'role_id' => $hrRole->id,
        ]);
    }
}
