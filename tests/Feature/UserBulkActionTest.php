<?php
// tests/Feature/UserBulkActionTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class UserBulkActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function bulk_action_route_is_defined_and_requires_authentication(): void
    {
        // Unauthenticated users should be redirected to login
        $response = $this->post(route('users.bulkAction'), [
            'action'       => 'lock',
            'selected_ids' => [1,2],
        ]);

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_gets_422_and_json_errors_when_payload_is_missing(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Send as JSON so we get a 422 with JSON validation errors
        $response = $this->postJson(route('users.bulkAction'), []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['action','selected_ids']);
    }

    #[Test]
    public function authenticated_user_can_lock_and_unlock_users_via_bulk_action(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        // Create three active users to operate on
        $targets = User::factory()->count(3)->create([
            'status' => 'active',
        ]);

        // LOCK them
        $lockResponse = $this->post(route('users.bulkAction'), [
            'action'       => 'lock',
            'selected_ids' => $targets->pluck('id')->all(),
        ]);

        // After a successful normal POST Laravel redirects back
        $lockResponse->assertStatus(302);

        // Confirm at least the first one is now inactive
        $this->assertDatabaseHas('users', [
            'id'     => $targets->first()->id,
            'status' => 'inactive',
        ]);

        // UNLOCK them
        $unlockResponse = $this->post(route('users.bulkAction'), [
            'action'       => 'unlock',
            'selected_ids' => $targets->pluck('id')->all(),
        ]);

        $unlockResponse->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'id'     => $targets->first()->id,
            'status' => 'active',
        ]);
    }
}
