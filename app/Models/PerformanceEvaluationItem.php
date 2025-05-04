<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceEvaluationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_evaluation_id',
        'metric',
        'weight',
        'actual',
        'notes',
    ];

    public function evaluation()
    {
        return $this->belongsTo(PerformanceEvaluation::class, 'performance_evaluation_id');
    }
}
