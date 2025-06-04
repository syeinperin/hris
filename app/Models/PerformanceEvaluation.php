<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceEvaluation extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'form_id',
        'employee_id',
        'evaluator_id',
        'evaluated_on',
        'total_score',
        'comments',
    ];

    /**
     * Cast attributes to native types.
     */
    protected $casts = [
        'evaluated_on' => 'datetime',  // ← this turns the string into a Carbon instance
    ];

    /**
     * The form this evaluation belongs to.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(PerformanceForm::class, 'form_id');
    }

    /**
     * The employee being evaluated.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * The user who performed the evaluation.
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /**
     * The individual score details for each criterion.
     */
    public function details(): HasMany
    {
        return $this->hasMany(PerformanceEvaluationDetail::class, 'evaluation_id');
    }
}
