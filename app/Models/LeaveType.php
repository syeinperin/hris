<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\LeaveAllocation;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',            // ← add this
        'name',
        'default_days',
        'description',
        'is_active',
    ];

    public function allocations()
    {
        return $this->hasMany(LeaveAllocation::class);
    }
}
