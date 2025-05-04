<?php
// app/Models/Deduction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    protected $fillable = [
        'employee_id',
        'description',
        'amount',
        'effective_from',
        'effective_until',
        'notes',
    ];

    /**
     * Cast these attributes to Carbon instances.
     */
    protected $casts = [
        'effective_from'  => 'date',
        'effective_until' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
