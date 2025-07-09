<?php

namespace App\Models\Discipline;

use Illuminate\Database\Eloquent\Model;

class InfractionReport extends Model
{
    protected $fillable = [
        'employee_id','reported_by','location','description',
        'incident_date','incident_time','similar_before',
        'similar_count','confidential','will_testify'
    ];

    public function employee() { return $this->belongsTo(\App\Models\Employee::class); }
    public function reporter() { return $this->belongsTo(\App\Models\User::class,'reported_by'); }
    public function investigators() { return $this->hasMany(InfractionInvestigator::class); }
    public function actions() { return $this->hasMany(\App\Models\Discipline\DisciplinaryAction::class); }
}
