<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'reference_no',
        'loan_type_id',
        'plan_id',
        'principal_amount',
        'interest_rate',
        'term_months',
        'total_payable',
        'monthly_amount',
        'next_payment_date',
        'status',
        'released_at',
    ];

    protected $casts = [
        'next_payment_date' => 'date',
        'released_at'       => 'datetime',
    ];

    // ── Generate a reference number automatically if not provided
    protected static function booted(): void
    {
        static::creating(function (Loan $loan) {
            if (empty($loan->reference_no)) {
                $loan->reference_no = static::generateReferenceNo();
            }
        });
    }

    public static function generateReferenceNo(): string
    {
        $prefix = 'LN'.Carbon::now()->format('ym'); // e.g. LN2508
        do {
            $ref = sprintf('%s-%04d', $prefix, random_int(0, 9999));
        } while (static::where('reference_no', $ref)->exists());

        return $ref;
    }

    public function employee() { return $this->belongsTo(Employee::class); }
    public function loanType() { return $this->belongsTo(LoanType::class, 'loan_type_id'); }
    public function plan()     { return $this->belongsTo(LoanPlan::class, 'plan_id'); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->next_payment_date->lt(Carbon::today());
    }
}
