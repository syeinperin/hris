<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dashboard_view_gets_all_expected_variables(): void
    {
        // ensure the 'hr' role exists in the permission table
        Role::create(['name' => 'hr']);

        // now create a user and assign them to 'hr'
        $user = User::factory()->create();
        $user->assignRole('hr');

        // hit the dashboard route
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertStatus(200);

        // must have endingCount, and no sendingCount
        $response->assertViewHas('endingCount');
        $response->assertViewMissing('sendingCount');

        // check the rest of your variables as well
        $response->assertViewHasAll([
            'employeeCount',
            'absentCount',
            'departmentCount',
            'designationCount',
            'announcements',
            'birthdays',
            'anniversaries',
        ]);
    }
}
