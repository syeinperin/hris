<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'name',
        'email',
        'gender',
        'dob',
        'status',
        'employment_type',
        'employment_start_date',
        'employment_end_date',
        'current_address',
        'permanent_address',
        'father_name',
        'mother_name',
        'previous_company',
        'job_title',
        'years_experience',
        'nationality',
        'department_id',
        'designation_id',
        'schedule_id',
        'fingerprint_id',
        'profile_picture',
        'gsis_id_no',
        'pagibig_id_no',
        'philhealth_tin_id_no',
        'sss_no',
        'tin_no',
        'agency_employee_no',
    ];

    protected $casts = [
        'dob'                   => 'date',
        'employment_start_date' => 'date',
        'employment_end_date'   => 'date',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function user()        { return $this->belongsTo(\App\Models\User::class); }
    public function department()  { return $this->belongsTo(\App\Models\Department::class); }
    public function designation() { return $this->belongsTo(\App\Models\Designation::class); }
    public function schedule()    { return $this->belongsTo(\App\Models\Schedule::class); }
    public function leaveAllocations()
    {
        return $this->hasMany(\App\Models\LeaveAllocation::class);
    }

    // ── Query Scopes ────────────────────────────────────────────────────

    /** Only active employees */
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    /** Only inactive employees */
    public function scopeInactive($q)
    {
        return $q->where('status', 'inactive');
    }

    /** Filter by department_id if provided */
    public function scopeDepartment($q, $deptId)
    {
        return $deptId ? $q->where('department_id', $deptId) : $q;
    }

    /** Filter by employment_type if provided */
    public function scopeType($q, $type)
    {
        return $type ? $q->where('employment_type', $type) : $q;
    }

    /** Search by code or name */
    public function scopeSearch($q, $term)
    {
        if (! $term) {
            return $q;
        }
        return $q->where(function($sub) use ($term) {
            $sub->where('employee_code', 'like', "%{$term}%")
                ->orWhere('name',        'like', "%{$term}%");
        });
    }

    // ── Boot Logic ────────────────────────────────────────────────────

    protected static function booted()
    {
        static::creating(function ($emp) {
            // auto‐generate code
            if (! $emp->employee_code) {
                do {
                    $code = 'EMP' . rand(10000, 99999);
                } while (self::where('employee_code', $code)->exists());
                $emp->employee_code = $code;
            }
            // auto‐fill name if empty
            if (empty($emp->name)) {
                $emp->name = trim(
                    ($emp->first_name ?? '') . ' ' .
                    ($emp->last_name  ?? '')
                );
            }
        });

        static::deleting(function ($emp) {
            if ($emp->user) {
                $emp->user->delete();
            }
        });
    }

    // ── Helpers ─────────────────────────────────────────────────────

    public function endDateWithin(int $days): bool
    {
        if (! $this->employment_end_date) return false;
        return Carbon::today()->lte($this->employment_end_date)
            && Carbon::today()->diffInDays($this->employment_end_date) <= $days;
    }

    public function isContractExpired(): bool
    {
        if (! $this->employment_end_date) return false;
        return Carbon::today()->greaterThan($this->employment_end_date);
    }

    public function getServiceYearsAttribute(): int
    {
        if (! $this->employment_start_date) return 0;
        return Carbon::today()->diffInYears($this->employment_start_date);
    }
}
