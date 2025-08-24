<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

use App\Models\Approval;
use App\Models\Attendance;
use App\Models\Loan;
use App\Models\Payslip;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Core
        'employee_code','user_id','email','first_name','middle_name','last_name','name',
        'gender','dob','status','employment_type','employment_start_date','employment_end_date',
        // Address split
        'current_street_address','current_city','current_province','current_postal_code','permanent_address',
        // Family & history
        'father_name','mother_name','previous_company','job_title','years_experience','nationality',
        // Relations
        'department_id','designation_id','schedule_id','fingerprint_id',
        // Profile
        'profile_picture','profile_updated_at',
        // Benefits
        'gsis_id_no','pagibig_id_no','philhealth_tin_id_no','sss_no','tin_no','agency_employee_no',
        // Bio-Data: personal
        'position_desired','application_date','city_address','provincial_address','telephone','cellphone',
        'birth_place','civil_status','citizenship','height','weight','religion','spouse','occupation',
        'name_of_children','children_birth_date','father_occupation','mother_occupation','languages_spoken',
        'emergency_contact_name','emergency_contact_address','emergency_contact_phone',
        // Bio-Data: education
        'elementary_school','elementary_year_graduated','high_school','high_school_year_graduated',
        'college','college_year_graduated','degree_received','special_skills',
        // Bio-Data: employment record
        'emp1_company','emp1_position','emp1_from','emp1_to','emp2_company','emp2_position','emp2_from','emp2_to',
        // Bio-Data: character references
        'char1_name','char1_position','char1_company','char1_contact','char2_name','char2_position',
        'char2_company','char2_contact',
        // Bio-Data: certificates
        'res_cert_no','res_cert_issued_at','res_cert_issued_on','nbi_no','passport_no',
    ];

    protected $casts = [
        'dob' => 'date',
        'employment_start_date' => 'date',
        'employment_end_date'   => 'date',
        'application_date'      => 'date',
        'children_birth_date'   => 'date',
        'emp1_from' => 'date','emp1_to' => 'date',
        'emp2_from' => 'date','emp2_to' => 'date',
        'res_cert_issued_on'    => 'date',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function user()       { return $this->belongsTo(\App\Models\User::class); }
    public function department() { return $this->belongsTo(\App\Models\Department::class); }
    public function designation(){ return $this->belongsTo(\App\Models\Designation::class); }
    public function schedule()   { return $this->belongsTo(\App\Models\Schedule::class); }
    public function leaveAllocations() { return $this->hasMany(\App\Models\LeaveAllocation::class); }

    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    /** Attendance rows for this employee. */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /** Convenience */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /** Employee → Payslips via user_id */
    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'user_id', 'user_id');
    }

    /** NEW: Disciplinary actions (violations & suspensions) */
    public function disciplinaryActions()
    {
        return $this->hasMany(\App\Models\DisciplinaryAction::class);
    }

    // ── Scopes ──────────────────────────────────────────────────────────
    public function scopeActive($q)    { return $q->where('status', 'active'); }
    public function scopeInactive($q)  { return $q->where('status', 'inactive'); }
    public function scopeDepartment($q, $deptId) { return $deptId ? $q->where('department_id', $deptId) : $q; }
    public function scopeType($q, $type) { return $type ? $q->where('employment_type', $type) : $q; }
    public function scopeSearch($q, $term)
    {
        if (!$term) return $q;
        return $q->where(function ($sub) use ($term) {
            $sub->where('employee_code', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }

    // ── Events ─────────────────────────────────────────────────────────
    protected static function booted()
    {
        static::creating(function ($emp) {
            if (!$emp->employee_code) {
                do { $code = 'EMP' . rand(10000, 99999); }
                while (self::where('employee_code', $code)->exists());
                $emp->employee_code = $code;
            }
            if (empty($emp->name)) {
                $emp->name = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
            }
        });

        static::deleting(function ($emp) {
            if ($emp->user) $emp->user->delete();
        });
    }

    // ── Helpers ─────────────────────────────────────────────────────────
    public function endDateWithin(int $days): bool
    {
        if (!$this->employment_end_date) return false;
        return Carbon::today()->lte($this->employment_end_date)
            && Carbon::today()->diffInDays($this->employment_end_date) <= $days;
    }

    public function isContractExpired(): bool
    {
        if (!$this->employment_end_date) return false;
        return Carbon::today()->greaterThan($this->employment_end_date);
    }

    public function getServiceYearsAttribute(): int
    {
        if (!$this->employment_start_date) return 0;
        return Carbon::today()->diffInYears($this->employment_start_date);
    }
}
