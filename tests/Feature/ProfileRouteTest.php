<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class ProfileRouteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_view_the_profile_edit_page()
    {
        $user = User::factory()->create();
        $dept = Department::create(['name' => 'HR']);
        $desig = Designation::create(['name' => 'Manager']);

        Employee::create([
            'user_id'             => $user->id,
            'first_name'          => 'Test',
            'last_name'           => 'User',
            'name'                => 'Test User',
            'email'               => $user->email,
            'gender'              => 'male',
            'dob'                 => Carbon::now()->subYears(30)->toDateString(),
            'current_address'     => '123 Main St',
            'permanent_address'   => '123 Main St',
            'department_id'       => $dept->id,
            'designation_id'      => $desig->id,
            'employment_type'     => 'regular',
            'employment_start_date'=> Carbon::now()->toDateString(),       // ← add this
            'employment_end_date' => Carbon::now()->addYear()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200)
                 ->assertViewIs('profile.edit');
    }

    /** @test */
    public function guest_getting_profile_is_redirected_to_login()
    {
        $this->get('/profile')
             ->assertRedirect('/login');
    }

    /** @test */
    public function update_form_contains_put_and_csrf_fields()
    {
        $user = User::factory()->create();
        $dept = Department::create(['name' => 'HR']);
        $desig = Designation::create(['name' => 'Manager']);

        Employee::create([
            'user_id'             => $user->id,
            'first_name'          => 'Test',
            'last_name'           => 'User',
            'name'                => 'Test User',
            'email'               => $user->email,
            'gender'              => 'male',
            'dob'                 => Carbon::now()->subYears(30)->toDateString(),
            'current_address'     => '123 Main St',
            'permanent_address'   => '123 Main St',
            'department_id'       => $dept->id,
            'designation_id'      => $desig->id,
            'employment_type'     => 'regular',
            'employment_start_date'=> Carbon::now()->toDateString(),       // ← and here
            'employment_end_date' => Carbon::now()->addYear()->toDateString(),
        ]);

        $html = $this->actingAs($user)
                     ->get('/profile')
                     ->getContent();

        $this->assertStringContainsString('name="_token"',            $html);
        $this->assertStringContainsString('name="_method" value="PUT"', $html);
    }
}
