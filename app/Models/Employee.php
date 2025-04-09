<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Schedule; // Import Schedule model

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'name', 'email',
        'gender', 'dob', 'current_address', 'permanent_address',
        'father_name', 'mother_name', 'previous_company',
        'job_title', 'years_experience', 'nationality',
        'department_id', 'designation_id', 'user_id', 'profile_picture',
        'fingerprint_id',
        'schedule_id' // Add schedule_id to allow mass assignment
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationship with Schedule model
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class, 'employee_id');
    }

    public function designations()
    {
        return $this->belongsTo(\App\Models\Designation::class, 'designation_id');
    }
}
