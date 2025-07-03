<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InactiveUser extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'employees';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'status',
        // add any other employee columns you need here
    ];

    /**
     * Add a local scope for convenience.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }
}
