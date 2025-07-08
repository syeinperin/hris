<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanType extends Model
{
    // if you're on Laravel ≥8 and using $guarded, you can use that instead
    protected $fillable = ['name'];

    /**
     * A loan type can have many loans.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
