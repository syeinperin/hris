<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DisciplinaryAction extends Model
{
    protected $fillable = [
        'employee_id',
        'issued_by',
        'action_type',   // 'violation' | 'suspension'
        'category',
        'severity',      // 'minor' | 'major' | 'critical'
        'points',
        'reason',
        'start_date',    // for suspension
        'end_date',      // for suspension
        'status',        // 'active' | 'resolved'
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

    // ── Helpers ─────────────────────────────────────────────────────────

    public function isViolation(): bool
    {
        return $this->action_type === 'violation';
    }

    public function isSuspension(): bool
    {
        return $this->action_type === 'suspension';
    }

    /**
     * Compute how many suspension days overlap a given [from, to] period.
     * Inclusive of both endpoints.
     */
    public function suspensionDaysInRange($from, $to): int
    {
        if (!$this->isSuspension() || !$this->start_date || !$this->end_date) {
            return 0;
        }

        $from = Carbon::parse($from)->startOfDay();
        $to   = Carbon::parse($to)->endOfDay();

        $start = Carbon::parse($this->start_date)->startOfDay();
        $end   = Carbon::parse($this->end_date)->endOfDay();

        // No overlap
        if ($end->lt($from) || $start->gt($to)) {
            return 0;
        }

        $overlapStart = $start->max($from);
        $overlapEnd   = $end->min($to);

        // inclusive days
        return $overlapStart->diffInDays($overlapEnd) + 1;
    }
}
