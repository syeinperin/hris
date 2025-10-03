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
        'current_street_address','current_city','current_barangay','current_province','current_postal_code',
        'permanent_address',

        // Family & history
        'father_name','mother_name','previous_company','job_title','years_experience','nationality',

        // Relations
        'department_id','designation_id','schedule_id','fingerprint_id',

        // Profile
        'profile_picture','profile_updated_at',

        // Benefits
        'gsis_id_no','pagibig_id_no','philhealth_tin_id_no','sss_no','tin_no','agency_employee_no',

        // Bio-Data: personal
        'position_desired','application_date','city_address','provincial_address','telephone',
        'contact_number',
        'birth_place','civil_status','citizenship','height','weight','religion','spouse','occupation',
        'name_of_children','children_birth_date','father_occupation','mother_occupation','languages_spoken',
        'emergency_contact_name','emergency_contact_address','emergency_contact_phone',

        // Bio-Data: education
        'elementary_school','elementary_year_graduated','high_school','high_school_year_graduated',
        'college','college_year_graduated','degree_received','special_skills',

        // Bio-Data: employment record
        'emp1_company','emp1_position','emp1_from','emp1_to',
        'emp2_company','emp2_position','emp2_from','emp2_to',

        // Bio-Data: character references
        'char1_name','char1_position','char1_company','char1_contact',
        'char2_name','char2_position','char2_company','char2_contact',

        // Certificates (legacy fields kept for compatibility)
        'res_cert_no','res_cert_issued_at','res_cert_issued_on','nbi_no','passport_no',

        // === Upload columns ===
        'resume_file','mdr_philhealth_file','mdr_sss_file','mdr_pagibig_file','medical_documents',
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

        // Cast JSON to array
        'medical_documents'     => 'array',
    ];

    /**
     * Extra computed attributes appended automatically
     */
    protected $appends = [
        'profile_picture_url',
        'resume_url',
        'mdr_philhealth_url',
        'mdr_sss_url',
        'mdr_pagibig_url',
        'medical_documents_urls',
    ];

    /* =========================================================
     * RELATIONSHIPS
     * ========================================================= */
    public function user()       { return $this->belongsTo(\App\Models\User::class); }
    public function department() { return $this->belongsTo(\App\Models\Department::class); }
    public function designation(){ return $this->belongsTo(\App\Models\Designation::class); }
    public function schedule()   { return $this->belongsTo(\App\Models\Schedule::class); }
    public function leaveAllocations() { return $this->hasMany(\App\Models\LeaveAllocation::class); }
    public function approvals()  { return $this->morphMany(Approval::class, 'approvable'); }
    public function attendances(){ return $this->hasMany(Attendance::class); }
    public function loans()      { return $this->hasMany(Loan::class); }
    public function payslips()   { return $this->hasMany(Payslip::class, 'user_id', 'user_id'); }
    public function disciplinaryActions() { return $this->hasMany(\App\Models\DisciplinaryAction::class); }
    public function documents()  { return $this->hasMany(\App\Models\Document::class); }

    // ✅ Offboarding relations
    public function offboardings()      { return $this->hasMany(\App\Models\Offboarding::class); }
    public function latestOffboarding() { return $this->hasOne(\App\Models\Offboarding::class)->latestOfMany(); }

    /* =========================================================
     * SCOPES
     * ========================================================= */
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

    /* =========================================================
     * EVENTS
     * ========================================================= */
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

    /* =========================================================
     * HELPERS
     * ========================================================= */
    public function endDateWithin(int $days): bool
    {
        if (!$this->employment_end_date) return false;
        return Carbon::today()->lte($this->employment_end_date)
            && Carbon::today()->diffInDays($this->employment_end_date) <= $days;
    }

    public function isContractExpired(): bool
    {
        return $this->employment_end_date
            ? Carbon::today()->greaterThan($this->employment_end_date)
            : false;
    }

    public function getServiceYearsAttribute(): int
    {
        return $this->employment_start_date
            ? Carbon::today()->diffInYears($this->employment_start_date)
            : 0;
    }

    /* =========================================================
     * FILE URL HELPERS
     * ========================================================= */
    protected function storageUrl(?string $path): ?string
    {
        return $path ? route('public.files', $path) : null;
    }

    public function getProfilePictureUrlAttribute(): ?string
    {
        return $this->storageUrl($this->profile_picture);
    }
    public function getResumeUrlAttribute(): ?string
    {
        return $this->storageUrl($this->resume_file);
    }
    public function getMdrPhilhealthUrlAttribute(): ?string
    {
        return $this->storageUrl($this->mdr_philhealth_file);
    }
    public function getMdrSssUrlAttribute(): ?string
    {
        return $this->storageUrl($this->mdr_sss_file);
    }
    public function getMdrPagibigUrlAttribute(): ?string
    {
        return $this->storageUrl($this->mdr_pagibig_file);
    }
    public function getMedicalDocumentsUrlsAttribute(): array
    {
        $docs = is_array($this->medical_documents) ? $this->medical_documents : [];
        return array_values(array_filter(array_map(fn ($p) => $this->storageUrl($p), $docs)));
    }
}
