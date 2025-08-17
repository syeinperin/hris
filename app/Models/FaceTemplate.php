<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceTemplate extends Model
{
    protected $fillable = [
        'employee_id',
        'descriptor',
        'image_path',
    ];

    protected $casts = [
        'descriptor' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }
}
