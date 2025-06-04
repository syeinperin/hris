<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagibigContribution extends Model
{
    protected $table = 'pagibig_contributions';
    protected $fillable = [
        'range_min',
        'range_max',
        'employee_share',
        'employer_share',
    ];
}
