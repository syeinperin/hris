<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'department_id', 'user_id', 'fingerprint_id']; // Added fingerprint_id

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department() 
    {
        return $this->belongsTo(Department::class);
    }

    public function attendanceRecords() 
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }
}
