<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LateDeduction extends Model
{
    protected $table = 'late_deductions';

    protected $fillable = [
        'mins_min',
        'mins_max',
        'multiplier',
        'description',
    ];

    protected $casts = [
        'mins_min'   => 'integer',
        'mins_max'   => 'integer',
        'multiplier' => 'decimal:2',
    ];
}
