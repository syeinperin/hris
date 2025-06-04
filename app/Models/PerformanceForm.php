<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceForm extends Model
{
    protected $fillable = [
        'title',
        'description',
        'created_by',
        'evaluator_id',
    ];

    /** Who created this form */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The supervisor assigned to evaluate */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    /** The questions/criteria for this form */
    public function criteria(): HasMany
    {
        return $this->hasMany(PerformanceCriterion::class, 'form_id');
    }

    /** Evaluation submissions against this form */
    public function evaluations(): HasMany
    {
        return $this->hasMany(PerformanceEvaluation::class, 'form_id');
    }

    /** Which employees were assigned */
    public function assignments(): HasMany
    {
        return $this->hasMany(PerformanceFormAssignment::class, 'form_id');
    }
}
