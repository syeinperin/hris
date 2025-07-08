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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function loanType()
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id');
    }

    public function plan()
    {
        return $this->belongsTo(LoanPlan::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active' 
            && $this->next_payment_date->lt(Carbon::today());
    }
}
