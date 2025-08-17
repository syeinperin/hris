<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class Schedule extends Model
{
    protected $fillable = [
        'name',
        'time_in',
        'time_out',
        'rest_day',
    ];

    // Static relation: employee.schedule_id
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
