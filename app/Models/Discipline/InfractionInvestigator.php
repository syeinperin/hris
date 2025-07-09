<?php

namespace App\Models\Discipline;

use Illuminate\Database\Eloquent\Model;

class InfractionInvestigator extends Model
{
    protected $fillable = ['infraction_report_id','user_id'];
    public function infraction() { return $this->belongsTo(InfractionReport::class,'infraction_report_id'); }
    public function investigator() { return $this->belongsTo(\App\Models\User::class,'user_id'); }
}
