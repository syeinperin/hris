<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhilhealthContribution extends Model
{
    protected $table = 'philhealth_contributions';
    protected $fillable = [
        'range_min',
        'range_max',
        'rate_percent',
    ];
}
