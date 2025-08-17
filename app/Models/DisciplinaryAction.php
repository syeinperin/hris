<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisciplinaryAction extends Model
{
    protected $fillable = [
        'employee_id',
        'issued_by',
        'action_type',
        'category',
        'severity',
        'points',
        'reason',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}

