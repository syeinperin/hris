<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'name', 'email',
        'gender', 'dob', 'current_address', 'permanent_address',
        'father_name', 'mother_name', 'previous_company',
        'job_title', 'years_experience', 'nationality',
        'department_id', 'designation_id', 'user_id', 'profile_picture',
        'fingerprint_id' // ✅ Add this to match your DB
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function user() // ✅ Add this
    {
        return $this->belongsTo(User::class);
    }
}
