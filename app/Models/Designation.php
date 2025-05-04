<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'rate_per_hour'];

    
    public function employees()
    {
        return $this->hasMany(\App\Models\Employee::class, 'designation_id');
    }
}
