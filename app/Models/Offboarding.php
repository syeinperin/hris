<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offboarding extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'type',
        'status',
        'effective_date',
        'reason',
        'allow_portal_access_until',
        'company_asset_returned',
        'separation_notes',
        'clearance',
        'exit_interview',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date'            => 'date',
        'allow_portal_access_until' => 'date',
        'company_asset_returned'    => 'boolean',
        'clearance'                 => 'array',
        'exit_interview'            => 'array',
    ];

    // Relations
    public function employee()
    {
        return $this->belongsTo(\App\Models\Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    // Convenience
    public function isFinal(): bool
    {
        return in_array($this->status, ['completed','cancelled'], true);
    }
}
