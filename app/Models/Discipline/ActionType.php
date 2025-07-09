<?php

namespace App\Models\Discipline;

use Illuminate\Database\Eloquent\Model;

class ActionType extends Model
{
    protected $fillable = [
        'code','description','severity_level',
        'outcome','suspension_policy','leave_days','status'
    ];
    public function actions() { return $this->hasMany(\App\Models\Discipline\DisciplinaryAction::class,'action_type_id'); }
}
