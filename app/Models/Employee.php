<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'email',
        'gender',
        'dob',
        'status',
        'employment_type',
        'employment_end_date',
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
        'schedule_id',
        'fingerprint_id',
        'profile_picture',
    ];

    /**
     * Cast date fields to Carbon instances.
     */
    protected $casts = [
        'dob'                 => 'date',
        'employment_end_date' => 'date',
    ];

    //───────────────────────────────────────────────────────────────────────────
    // Relationships
    //───────────────────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(\App\Models\Designation::class);
    }

    public function schedule()
    {
        return $this->belongsTo(\App\Models\Schedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    public function deductions()
    {
        return $this->hasMany(\App\Models\Deduction::class);
    }

    //───────────────────────────────────────────────────────────────────────────
    // Model Events (auto-generate code, cascade delete)
    //───────────────────────────────────────────────────────────────────────────

    protected static function booted()
    {
        static::creating(function ($employee) {
            // Generate a unique employee_code if not already set
            if (! $employee->employee_code) {
                do {
                    $code = 'EMP' . rand(10000, 99999);
                } while (self::where('employee_code', $code)->exists());
                $employee->employee_code = $code;
            }
        });

        static::deleting(function ($employee) {
            // Delete the linked user when an employee is deleted
            if ($employee->user) {
                $employee->user->delete();
            }
        });
    }

    //───────────────────────────────────────────────────────────────────────────
    // Helper Methods
    //───────────────────────────────────────────────────────────────────────────

    /**
     * Check if employment_end_date is within the next $days days (inclusive).
     */
    public function endDateWithin(int $days): bool
    {
        if (! $this->employment_end_date) {
            return false;
        }
        $today = Carbon::today();
        return $this->employment_end_date->between($today, $today->copy()->addDays($days));
    }

    /**
     * Check if employment_end_date has already passed.
     */
    public function isContractExpired(): bool
    {
        if (! $this->employment_end_date) {
            return false;
        }
        return Carbon::today()->greaterThan($this->employment_end_date);
    }
}
