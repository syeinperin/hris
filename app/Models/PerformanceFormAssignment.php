<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceFormAssignment extends Model
{
    protected $fillable = [
        'form_id',
        'employee_id',
        'evaluator_id',
        'starts_at',   // ← NEW
        'ends_at',     // ← NEW
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
    ];

    /** The form this assignment belongs to */
    public function form(): BelongsTo
    {
        return $this->belongsTo(PerformanceForm::class, 'form_id');
    }

    /** The employee being evaluated */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /** The supervisor who will evaluate */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}
