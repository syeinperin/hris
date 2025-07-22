<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Info – {{ $employee->employee_code }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align:center; margin-bottom:20px; }
        .header img { max-height:50px; margin-bottom:5px; }
        .title { font-size:18px; margin:0; }
        .date  { font-size:12px; color:#666; }
        .section-title { background:#004677; color:#fff; padding:4px 8px; margin-top:20px; font-size:14px; }
        table { width:100%; border-collapse:collapse; margin-top:8px; }
        td { padding:4px; vertical-align:top; }
        .label { width:20%; font-weight:bold; }
    </style>
</head>
<body>
  <div class="header">
    @if(config('app.logo'))
      <img src="{{ public_path('storage/'.config('app.logo')) }}" alt="Logo">
    @endif
    <p class="title">Employee Information Sheet</p>
    <p class="date">{{ now()->format('j/n/Y') }}</p>
  </div>

  <div>
    <div class="section-title">Personal Information</div>
    <table>
      <tr>
        <td class="label">Name</td><td>{{ $employee->name }}</td>
        <td class="label">Employee ID</td><td>{{ $employee->employee_code }}</td>
      </tr>
      <tr>
        <td class="label">Email</td><td>{{ $employee->email }}</td>
        <td class="label">Cell Phone</td><td>{{ $employee->phone ?? '–' }}</td>
      </tr>
      <tr>
        <td class="label">Address</td><td colspan="3">{{ $employee->address ?? '–' }}</td>
      </tr>
      <tr>
        <td class="label">Birth Date</td><td>{{ optional($employee->birth_date)->format('j/n/Y') }}</td>
        <td class="label">Marital Status</td><td>{{ $employee->marital_status ?? '–' }}</td>
      </tr>
      <tr>
        <td class="label">Spouse’s Name</td><td>{{ $employee->spouse_name ?? '–' }}</td>
        <td class="label">Spouse’s Phone</td><td>{{ $employee->spouse_phone ?? '–' }}</td>
      </tr>
    </table>
  </div>

  <div>
    <div class="section-title">Job Information</div>
    <table>
      <tr>
        <td class="label">Department</td><td>{{ optional($employee->department)->name }}</td>
        <td class="label">Position</td><td>{{ optional($employee->designation)->name }}</td>
      </tr>
      <tr>
        <td class="label">Start Date</td><td>{{ optional($employee->start_date)->format('j/n/Y') }}</td>
        <td class="label">Salary</td><td>{{ number_format($employee->salary,2) }}</td>
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
