<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'evaluator_id',
        'performance_plan_id',  // ← add this
        'evaluation_date',
        'status',
        'comments',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function plan()
    {
        // points at performance_plan_id → id on PerformancePlan
        return $this->belongsTo(PerformancePlan::class, 'performance_plan_id');
    }
}
