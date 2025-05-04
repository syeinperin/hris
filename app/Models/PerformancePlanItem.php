<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformancePlanItem extends Model
{
    use HasFactory;

    protected $fillable = ['performance_plan_id','metric','weight'];

    public function plan()
    {
        return $this->belongsTo(PerformancePlan::class, 'performance_plan_id');
    }
}
