<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PerformanceEvaluation extends Model
{
    protected $fillable = [
        'employee_id','evaluator_id','period_start','period_end',
        'overall_score','remarks','status'
    ];
    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'overall_score'=> 'decimal:2',
    ];
    public function employee(){ return $this->belongsTo(Employee::class); }
    public function evaluator(){ return $this->belongsTo(User::class, 'evaluator_id'); }
    public function scores(){ return $this->hasMany(PerformanceScore::class, 'evaluation_id'); }
}
