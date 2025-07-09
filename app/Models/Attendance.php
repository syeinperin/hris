<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'schedule_id',
        'time_in',
        'time_out',
        'is_manual',
    ];

    protected $casts = [
        'time_in'   => 'datetime',
        'time_out'  => 'datetime',
        'is_manual' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /** Handy accessor to group by date */
    public function getDayAttribute()
    {
        return $this->time_in
                    ? $this->time_in->toDateString()
                    : null;
    }
}