<?php

// app/Models/Employee.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Schedule;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'email',
        'gender',
        'dob',
        'current_address',
        'permanent_address',
        'father_name',
        'mother_name',
        'previous_company',
        'job_title',
        'years_experience',
        'nationality',
        'department_id',
        'designation_id',
        'user_id',
        'profile_picture',
        'fingerprint_id',
        'schedule_id',
        'employee_code',
        'status',
        'employment_type',  // ← Added so that employment_type can be mass‐assigned
    ];

    // Relationships

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
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

    public function deductions()
    {
        return $this->hasMany(\App\Models\Deduction::class);
    }

    /**
     * Booted event to auto-generate a custom employee code.
     */
    protected static function booted()
    {
        static::creating(function ($employee) {
            if (! $employee->employee_code) {
                do {
                    $code = 'EMP' . rand(10000, 99999);
                } while (self::where('employee_code', $code)->exists());
                $employee->employee_code = $code;
            }
        });

        static::deleting(function ($employee) {
            if ($employee->user) {
                $employee->user->delete();
            }
        });
    }
}
