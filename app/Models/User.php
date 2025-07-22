<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for arrays / JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login'        => 'datetime',
    ];

    /**
     * Belongs to a domain Role.
     */
    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }

    /**
     * One‐to‐one link to the Employee record.
     */
    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class, 'user_id', 'id');
    }

    /**
     * All payslips generated for this user.
     */
    public function payslips()
    {
        return $this->hasMany(\App\Models\Payslip::class);
    }

    /**
     * All leave requests filed by this user.
     */
    public function leaveRequests()
    {
        return $this->hasMany(\App\Models\LeaveRequest::class);
    }

    /**
     * Simple name‐based role check against domain roles.
     */
    public function hasRoleName(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Override for the database channel so it uses the notifications() relation.
     */
    public function routeNotificationForDatabase($notification = null)
    {
        return $this->notifications();
    }
}
