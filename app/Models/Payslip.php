<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_start',
        'period_end',
        'worked_hours',
        'ot_hours',
        'ot_pay',
        'deductions',
        'gross_amount',
        'net_amount',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
