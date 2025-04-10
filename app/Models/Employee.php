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
        'fingerprint_id', 'schedule_id'
    ];

    // Relationships
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
     * Booted method to automatically delete the related User when the Employee is deleted.
     */
    protected static function booted()
    {
        static::deleting(function ($employee) {
            if ($employee->user) {
                $employee->user->delete();
            }
        });
    }
}
