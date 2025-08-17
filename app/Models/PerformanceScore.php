<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PerformanceScore extends Model
{
    protected $fillable = ['evaluation_id','item_id','score','notes','weight_cache','weighted_score'];
    public function evaluation(){ return $this->belongsTo(PerformanceEvaluation::class, 'evaluation_id'); }
    public function item(){ return $this->belongsTo(PerformanceItem::class, 'item_id'); }
}
