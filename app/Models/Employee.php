<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Schedule;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'name', 'email',
        'gender', 'dob', 'current_address', 'permanent_address',
        'father_name', 'mother_name', 'previous_company',
        'job_title', 'years_experience', 'nationality',
        'department_id', 'designation_id', 'user_id', 'profile_picture',
        'fingerprint_id', 'schedule_id', 'employee_code'
    ];

    // Relationships

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class, 'employee_id');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(\App\Models\Designation::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Booted event to auto-generate a custom employee code.
     */
    protected static function booted()
    {
        static::creating(function ($employee) {
            // If employee_code is not already provided, generate one.
            if (!$employee->employee_code) {
                do {
                    // Generate a code with a fixed prefix and a random five-digit number.
                    $code = 'EMP' . rand(10000, 99999);
                } while (self::where('employee_code', $code)->exists());
                $employee->employee_code = $code;
            }
        });

        // Optionally, when an employee is deleted, delete the associated user.
        static::deleting(function ($employee) {
            if ($employee->user) {
                $employee->user->delete();
            }
        });
    }
}
