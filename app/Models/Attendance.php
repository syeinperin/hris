<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'time_in',
        'time_out',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
