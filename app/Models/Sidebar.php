<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sidebar extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'route', 'icon', 'parent_id', 'order', 'role'];

    protected $casts = [
        'role' => 'array',
    ];

    public function children()
    {
        return $this->hasMany(Sidebar::class, 'parent_id');
    }
}


