<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PerformanceItem extends Model
{
    protected $fillable = ['name','weight','description','is_active','display_order'];
    protected $casts = ['is_active' => 'boolean'];
    public function scores(){ return $this->hasMany(PerformanceScore::class, 'item_id'); }
}
