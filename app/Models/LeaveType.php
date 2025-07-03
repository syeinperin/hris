<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LeaveAllocation;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_days',
        'description',
        'is_active',
    ];

    /**
     * A leave type has many allocations.
     */
    public function allocations()
    {
        return $this->hasMany(LeaveAllocation::class);
    }
}
