<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'time_in',
        'time_out',
    ];

    /**
     * Cast time_in/time_out to Carbon instances.
     */
    protected $casts = [
        'time_in'  => 'datetime',
        'time_out' => 'datetime',
    ];

    /**
     * Each attendance belongs to one employee.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
