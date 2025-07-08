<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanPlan extends Model
{
    protected $fillable = [
        'name',
        'months',
        'interest_rate',
    ];

    /**
     * A plan can be assigned to many loans.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'plan_id');
    }
}
