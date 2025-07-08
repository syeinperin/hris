<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceEvaluationDetail extends Model
{
    // Full‐word rating constants
    public const RATING_NEEDS_IMPROVEMENT = 'Needs Improvement';
    public const RATING_UNSATISFACTORY    = 'Unsatisfactory';
    public const RATING_FAIR              = 'Fair';
    public const RATING_SATISFACTORY      = 'Satisfactory';
    public const RATING_GOOD              = 'Good';
    public const RATING_EXCELLENT         = 'Excellent';

    protected $fillable = [
        'evaluation_id',
        'criterion_id',
        'rating',   // full string
        'comments', // per‐criterion remarks
    ];

    // Automatically append this to arrays/JSON
    protected $appends = ['weighted_score'];

    /**
     * Compute weighted_score = default_score × (rating_index / max_index).
     * e.g. default_score=40, rating_index=4, max_index=5 ⇒ 40*(4/5)=32
     */
    public function getWeightedScoreAttribute(): int
    {
        $default = $this->criterion->default_score ?? 0;
        $keys    = array_keys(self::ratingOptions());
        $pos     = array_search($this->rating, $keys, true);

        if ($pos === false) {
            return 0;
        }

        $maxIndex = count($keys) - 1;
        if ($maxIndex < 1) {
            return 0;
        }

        // Scale and round to nearest integer
        return (int) round($default * ($pos / $maxIndex));
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(PerformanceEvaluation::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(PerformanceCriterion::class);
    }

    /**
     * Map rating keys → human labels
     */
    public static function ratingOptions(): array
    {
        return [
            self::RATING_NEEDS_IMPROVEMENT => 'Needs Improvement',
            self::RATING_UNSATISFACTORY    => 'Unsatisfactory',
            self::RATING_FAIR              => 'Fair',
            self::RATING_SATISFACTORY      => 'Satisfactory',
            self::RATING_GOOD              => 'Good',
            self::RATING_EXCELLENT         => 'Excellent',
        ];
    }
}
