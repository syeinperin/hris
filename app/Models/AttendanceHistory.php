<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceHistory extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'scanned_at',
        'status',
        'type',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
