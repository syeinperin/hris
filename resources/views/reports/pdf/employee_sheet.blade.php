<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Info – {{ $employee->employee_code }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-height: 50px; margin-bottom: 5px; }
        .title { font-size: 18px; margin: 0; }
        .date  { font-size: 12px; color: #666; }
        .section-title { background: #004677; color: #fff; padding: 4px 8px; margin-top: 20px; font-size: 14px; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        td { padding:4px; vertical-align:top; }
        .label { width:25%; font-weight:bold; }
    </style>
</head>
<body>
    <div class="header">
        @if(config('app.logo') && file_exists(public_path('storage/' . config('app.logo'))))
            <img src="{{ public_path('storage/' . config('app.logo')) }}" alt="Logo">
        @endif
        <p class="title">Employee Information Sheet</p>
        <p class="date">{{ now()->format('j/n/Y') }}</p>
    </div>

    {{-- ─── Employee Details ─────────────────────── --}}
    <div>
      <div class="section-title">Employee Details</div>
      <table>
        <tr>
          <td class="label">Full Name</td>
          <td>{{ $employee->name ?? '–' }}</td>
          <td class="label">Employee Code</td>
          <td>{{ $employee->employee_code ?? '–' }}</td>
        </tr>
        <tr>
          <td class="label">Role</td>
          <td>{{ data_get($employee, 'user.roles.0.name', '–') }}</td>
          <td class="label">Account Status</td>
          <td>{{ ucfirst(data_get($employee, 'user.status', '–')) }}</td>
        </tr>
        <tr>
          <td class="label">Last Login</td>
          <td>
            @php $lastLogin = data_get($employee, 'user.last_login_at'); @endphp
            {{ $lastLogin ? \Carbon\Carbon::parse($lastLogin)->format('j/n/Y H:i') : '–' }}
          </td>
          <td class="label">Gender</td>
          <td>{{ ucfirst($employee->gender ?? '–') }}</td>
        </tr>
        <tr>
          <td class="label">DOB</td>
          <td>{{ optional($employee->birth_date)->format('j/n/Y') ?? '–' }}</td>
          <td class="label">Employment Type</td>
          <td>{{ ucfirst($employee->employment_type ?? '–') }}</td>
        </tr>
        <tr>
          <td class="label">Employment Status</td>
          <td>{{ ucfirst($employee->employment_status ?? '–') }}</td>
          <td class="label">Fingerprint ID</td>
          <td>{{ $employee->fingerprint_id ?? '–' }}</td>
        </tr>
      </table>
    </div>

    {{-- ─── Addresses ────────────────────────────── --}}
    <div>
      <div class="section-title">Addresses</div>
      <table>
        <tr>
          <td class="label">Current Address</td>
          <td colspan="3">{{ $employee->current_address ?? '–' }}</td>
        </tr>
        <tr>
          <td class="label">Permanent Address</td>
          <td colspan="3">{{ $employee->permanent_address ?? '–' }}</td>
        </tr>
      </table>
    </div>

    {{-- ─── Family & Previous Employment ───────── ─ --}}
    <div>
      <div class="section-title">Family & Previous Employment</div>
      <table>
        <tr>
          <td class="label">Father Name</td><td>{{ $employee->father_name ?? '–' }}</td>
          <td class="label">Mother Name</td><td>{{ $employee->mother_name ?? '–' }}</td>
        </tr>
        <tr>
          <td class="label">Previous Company</td><td>{{ $employee->previous_company ?? '–' }}</td>
          <td class="label">Previous Job Title</td><td>{{ $employee->previous_job_title ?? '–' }}</td>
        </tr>
        <tr>
          <td class="label">Experience (yrs)</td><td>{{ $employee->experience_years ?? '–' }}</td>
          <td class="label">Nationality</td><td>{{ $employee->nationality ?? '–' }}</td>
        </tr>
      </table>
    </div>

    {{-- ─── Government IDs ──────────────────────── --}}
    <div>
      <div class="section-title">Government / Benefit IDs</div>
      <table>
        <tr>
          <td class="label">GSIS ID No.</td><td>{{ $employee->gsis_id_no ?? '–' }}</td>
          <td class="label">Pag-IBIG ID No.</td><td>{{ $employee->pagibig_id_no ?? '–' }}</td>
        </tr>
        <tr>
          <td class="label">PhilHealth No.</td><td>{{ $employee->philhealth_no ?? '–' }}</td>
          <td class="label">TIN No.</td><td>{{ $employee->tin_no ?? '–' }}</td>
        </tr>
        <tr>
          <td class="label">SSS No.</td><td>{{ $employee->sss_no ?? '–' }}</td>
          <td class="label">Agency Emp. No.</td><td>{{ $employee->agency_employee_no ?? '–' }}</td>
        </tr>
      </table>
    </div>

    {{-- ─── Job & Emergency Info ─────────────────── --}}
    <div>
      <div class="section-title">Job Information</div>
      <table>
        <tr>
          <td class="label">Department</td><td>{{ optional($employee->department)->name ?? '–' }}</td>
          <td class="label">Position</td><td>{{ optional($employee->designation)->name ?? '–' }}</td>
        </tr>
        <tr>
          <td class="label">Start Date</td><td>{{ optional($employee->start_date)->format('j/n/Y') ?? '–' }}</td>
          <td class="label">Salary</td><td>{{ $employee->salary ? number_format($employee->salary,2) : '–' }}</td>
        </tr>
        <tr>
          <td class="label">Supervisor</td><td>{{ optional($employee->supervisor)->name ?? '–' }}</td>
          <td class="label">Work Location</td><td>{{ $employee->work_location ?? '–' }}</td>
        </tr>
      </table>
    </div>

    <div>
      <div class="section-title">Emergency Contact</div>
      <table>
        <tr>
          <td class="label">Name</td><td>{{ $employee->emergency_contact_name ?? '–' }}</td>
          <td class="label">Relationship</td><td>{{ $employee->emergency_contact_relation ?? '–' }}</td>
        </tr>
        <tr>
          <td class="label">Phone</td><td>{{ $employee->emergency_contact_phone ?? '–' }}</td>
          <td class="label">Address</td><td>{{ $employee->emergency_contact_address ?? '–' }}</td>
        </tr>
      </table>
    </div>
</body>
</html>
