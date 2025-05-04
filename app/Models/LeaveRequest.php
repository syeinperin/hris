<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * Who filed this request?
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
     public function employee()
    {
        // employee.user_id → users.id
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }
}
