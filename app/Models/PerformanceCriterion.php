<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceCriterion extends Model
{
    protected $fillable = ['form_id','text','default_score'];

    public function form() {
        return $this->belongsTo(PerformanceForm::class, 'form_id');
    }
}
