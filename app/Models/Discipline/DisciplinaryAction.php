<?php

namespace App\Models\Discipline;

use Illuminate\Database\Eloquent\Model;

class DisciplinaryAction extends Model
{
    protected $fillable = [
        'infraction_report_id','action_type_id','action_date','notes'
    ];
    public function infraction() { return $this->belongsTo(InfractionReport::class,'infraction_report_id'); }
    public function type() { return $this->belongsTo(ActionType::class,'action_type_id'); }
}
