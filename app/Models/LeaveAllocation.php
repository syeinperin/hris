<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_type_id',
        'employee_id',
        'year',
        'days_allocated',  // now truly days
        'days_used',       // truly days used
    ];

    /**
     * Entitled in days (no conversion).
     */
    public function getEntitledDaysAttribute(): float
    {
        return round($this->days_allocated, 2);
    }

    /**
     * Taken in days.
     */
    public function getTakenDaysAttribute(): float
    {
        return round($this->days_used, 2);
    }

    /**
     * Balance in days.
     */
    public function getBalanceDaysAttribute(): float
    {
        return round($this->days_allocated - $this->days_used, 2);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
