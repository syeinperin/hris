<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SssContribution extends Model
{
    protected $table = 'sss_contributions';
    protected $fillable = [
        'range_min',
        'range_max',
        'employee_share',
        'employer_share',
    ];
}
