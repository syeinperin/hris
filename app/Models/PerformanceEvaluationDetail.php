<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEvaluationDetail extends Model
{
    // Rating constants
    public const RATING_NEEDS_IMPROVEMENT = 'N';
    public const RATING_UNSATISFACTORY     = 'U';
    public const RATING_FAIR               = 'F';
    public const RATING_SATISFACTORY       = 'S';
    public const RATING_GOOD               = 'G';
    public const RATING_EXCELLENT          = 'E';

    protected $fillable = [
        'evaluation_id',
        'criterion_id',
        'rating',
        'comments',
    ];

    /**
     * Return [letter => label] for use in forms / headers.
     */
    public static function ratingOptions(): array
    {
        return [
            self::RATING_NEEDS_IMPROVEMENT => 'Needs Improvement',
            self::RATING_UNSATISFACTORY     => 'Unsatisfactory',
            self::RATING_FAIR               => 'Fair',
            self::RATING_SATISFACTORY       => 'Satisfactory',
            self::RATING_GOOD               => 'Good',
            self::RATING_EXCELLENT          => 'Excellent',
        ];
    }

    /**
     * The parent evaluation.
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(PerformanceEvaluation::class, 'evaluation_id');
    }

    /**
     * The criterion being rated.
     */
    public function criterion(): BelongsTo
    {
        return $this->belongsTo(PerformanceCriterion::class, 'criterion_id');
    }
}
