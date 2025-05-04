<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\LeaveRequest;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Mass‐assignable attributes
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'last_login',
    ];

    // Hidden for arrays / JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Casts
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login'        => 'datetime',
    ];

    /**
     * Belongs to a Role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * One‐to‐one link to the Employee record.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

    /**
     * All payslips generated for this user.
     */
    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    /**
     * All leave requests filed by this user.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Simple name‐based role check.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }
}
