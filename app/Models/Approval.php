<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'approvable_id',
        'approvable_type',
        'status',        // 'pending' | 'approved' | 'rejected'
        'requested_by',  // <-- aligned to your controllers & DB column
        'approver_id',
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
