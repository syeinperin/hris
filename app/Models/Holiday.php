<?php
// app/Models/Holiday.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'type',
        'is_recurring',
    ];

    // cast date ⇒ Carbon, is_recurring ⇒ bool
    protected $casts = [
        'date'         => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Scope for fetching holidays in a specific year.
     */
    public function scopeForYear($query, $year = null)
    {
        $year = $year ?: Carbon::now()->year;
        return $query->whereYear('date', $year);
    }
}
