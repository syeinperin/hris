<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
    ];

    // Relationship: Each user has one employee detail.
    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class);
    }

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }
}
