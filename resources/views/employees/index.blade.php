@extends('layouts.app')

@section('page_title', 'Employees')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h4 class="mb-0">
        <i class="bi bi-people-fill me-2"></i> Employees
      </h4>
      <div class="d-flex align-items-center">
        <!-- Ending Soon -->
        <a href="{{ route('employees.endings') }}"
           class="btn btn-outline-warning btn-sm me-2">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Ending Soon
          <span class="badge bg-warning text-dark">{{ $endingCount }}</span>
        </a>

        <!-- Inactive -->
        <a href="{{ route('employees.inactive') }}"
           class="btn btn-outline-secondary btn-sm me-2">
          <i class="bi bi-person-x me-1"></i>
          Inactive
          <span class="badge bg-secondary">{{ $inactiveCount }}</span>
        </a>

        <!-- Departments -->
        <a href="{{ route('departments.index') }}"
           class="btn btn-outline-primary btn-sm me-2">
          <i class="bi bi-building me-1"></i>
          Departments
        </a>

        <!-- Add Employee -->
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
          <i class="bi bi-plus-lg me-1"></i> Add
        </button>
      </div>
    </div>

    <div class="card-body">
      {{-- Filters --}}
      <form method="GET" action="{{ route('employees.index') }}" class="row g-3 mb-4">
        <div class="col-md-3">
          <select name="department_id" class="form-select">
            <option value="">All Departments</option>
            @foreach($departments as $id => $name)
              <option value="{{ $id }}" {{ request('department_id') == $id ? 'selected':'' }}>
                {{ $name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="employment_type" class="form-select">
            <option value="">All Types</option>
            @foreach($employmentTypes as $key => $label)
              <option value="{{ $key }}" {{ request('employment_type') == $key ? 'selected':'' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <input
            type="text"
            name="search"
            class="form-control"
            placeholder="Search name, code or email…"
            value="{{ request('search') }}"
          >
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill">Search</button>
          <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
        </div>
      </form>

      {{-- Table --}}
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Code</th>
              <th>Employment Status</th>
              <th>Name</th>
              <th>Email</th>
              <th>Dept</th>
              <th>Type</th>
              <th>Schedule</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($employees as $e)
              @php
                // prepare data for “view” modal
                $modal = [
                  'employee_code'     => $e->employee_code,
                  'first_name'        => $e->first_name,
                  'middle_name'       => $e->middle_name,
                  'last_name'         => $e->last_name,
                  'full_name'         => $e->name,
                  'gender'            => ucfirst($e->gender),
                  'dob'               => optional($e->dob)->format('Y-m-d'),
                  'employment_type'   => ucfirst($e->employment_type),
                  'employment_status' => ucfirst($e->status),
                  'start_date'        => optional($e->employment_start_date)->format('Y-m-d'),
                  'end_date'          => optional($e->employment_end_date)->format('Y-m-d'),
                  'department'        => $e->department->name ?? '—',
                  'designation'       => $e->designation->name ?? '—',
                  'schedule_name'     => $e->schedule->name ?? '—',
                  'schedule_in'       => $e->schedule?->time_in,
                  'schedule_out'      => $e->schedule?->time_out,
                  'fingerprint_id'    => $e->fingerprint_id,
                  // ...other fields...
                  'email'             => $e->user->email,
                  'role'              => ucfirst($e->user->role->name ?? $e->user->role_id),
                  'account_status'    => ucfirst($e->user->status),
                  'last_login'        => optional($e->user->last_login_at)->format('Y-m-d H:i:s'),
                ];
              @endphp
              <tr>
                <td>{{ $e->id }}</td>
                <td>{{ $e->employee_code }}</td>
                <td><span class="badge bg-primary">{{ ucfirst($e->status) }}</span></td>
                <td>{{ $e->name }}</td>
                <td>{{ $e->user->email }}</td>

                {{-- Department as a button to department edit --}}
                <td class="text-center">
                  @if($e->department)
                    <a href="{{ route('departments.edit', $e->department) }}"
                       class="btn btn-sm btn-outline-secondary">
                      {{ $e->department->name }}
                    </a>
                  @else
                    &mdash;
                  @endif
                </td>

                <td>{{ ucfirst($e->employment_type) }}</td>
                <td>
                  @if($e->schedule)
                    {{ $e->schedule->time_in }}–{{ $e->schedule->time_out }}
                  @else
                    &mdash;
                  @endif
                </td>
                <td class="text-center">
                  <button
                    type="button"
                    class="btn btn-outline-primary btn-sm rounded-circle me-1"
                    data-bs-toggle="modal"
                    data-bs-target="#viewEmployeeModal"
                    data-employee='@json($modal)'>
                    <i class="bi bi-eye"></i>
                  </button>

                  <a href="{{ route('employees.edit', $e) }}"
                     class="btn btn-outline-warning btn-sm rounded-circle me-1">
                    <i class="bi bi-pencil"></i>
                  </a>

                  <form action="{{ route('employees.destroy', $e) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm rounded-circle">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  No employees found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-4">
        <small class="text-muted">
          Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}
        </small>
        {{ $employees->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

{{-- Add Employee Modal --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Add Employee</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="max-height: calc(100vh - 180px); overflow-y: auto;">
          @if($errors->any())
            <div class="alert alert-danger">
              <strong>Oops—please fix the following:</strong>
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Tabs --}}
          <ul class="nav nav-tabs mb-4" id="empTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#account">Account</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal">Personal</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#work">Work</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#benefits">Benefits</button></li>
          </ul>
          <div class="tab-content" id="empTabContent">
            {{-- ACCOUNT --}}
            <div class="tab-pane fade show active" id="account">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="email" name="email" id="email"
                         class="form-control @error('email') is-invalid @enderror"
                         placeholder="Email *" value="{{ old('email') }}" required>
                  <label for="email">Email *</label>
                  @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2 form-floating">
                  <input type="password" name="password" id="password"
                         class="form-control @error('password') is-invalid @enderror"
                         placeholder="Password *" required>
                  <label for="password">Password *</label>
                  @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2 form-floating">
                  <input type="password" name="password_confirmation"
                         class="form-control" placeholder="Confirm *" required>
                  <label>Confirm *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="role" id="role"
                          class="form-select @error('role') is-invalid @enderror" required>
                    <option value="" disabled {{ old('role')?'':'selected' }}>-- Select Role --</option>
                    @foreach($roles as $r)
                      <option value="{{ $r }}" {{ old('role')==$r?'selected':'' }}>
                        {{ ucfirst($r) }}
                      </option>
                    @endforeach
                  </select>
                  <label for="role">Role *</label>
                  @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>

            {{-- PERSONAL --}}
            <div class="tab-pane fade" id="personal">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="text" name="first_name" id="first_name"
                         class="form-control @error('first_name') is-invalid @enderror"
                         placeholder="First Name *" value="{{ old('first_name') }}" required>
                  <label for="first_name">First Name *</label>
                  @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="middle_name" id="middle_name"
                         class="form-control @error('middle_name') is-invalid @enderror"
                         placeholder="Middle Name" value="{{ old('middle_name') }}">
                  <label for="middle_name">Middle Name</label>
                  @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="last_name" id="last_name"
                         class="form-control @error('last_name') is-invalid @enderror"
                         placeholder="Last Name *" value="{{ old('last_name') }}" required>
                  <label for="last_name">Last Name *</label>
                  @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="current_address" id="current_address"
                         class="form-control @error('current_address') is-invalid @enderror"
                         placeholder="Current Address *" value="{{ old('current_address') }}" required>
                  <label for="current_address">Current Address *</label>
                  @error('current_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="permanent_address" id="permanent_address"
                         class="form-control @error('permanent_address') is-invalid @enderror"
                         placeholder="Permanent Address" value="{{ old('permanent_address') }}">
                  <label for="permanent_address">Permanent Address</label>
                  @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <select name="gender" id="gender"
                          class="form-select @error('gender') is-invalid @enderror" required>
                    <option value="" disabled {{ old('gender')?'':'selected' }}>Gender…</option>
                    <option value="male"   {{ old('gender')=='male'?'selected':'' }}>Male</option>
                    <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
                    <option value="other"  {{ old('gender')=='other'?'selected':'' }}>Other</option>
                  </select>
                  <label for="gender">Gender *</label>
                  @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="date" name="dob" id="dob"
                         class="form-control @error('dob') is-invalid @enderror"
                         placeholder="Date of Birth *" value="{{ old('dob') }}" required>
                  <label for="dob">Date of Birth *</label>
                  @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label">Profile Picture</label>
                  <input type="file" name="profile_picture"
                         class="form-control @error('profile_picture') is-invalid @enderror"
                         accept="image/*">
                  @error('profile_picture')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>

            {{-- WORK --}}
            <div class="tab-pane fade" id="work">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <select name="department_id" id="department"
                          class="form-select @error('department_id') is-invalid @enderror" required>
                    <option value="" disabled {{ old('department_id')?'':'selected' }}>Department *</option>
                    @foreach($departments as $id => $name)
                      <option value="{{ $id }}" {{ old('department_id')==$id?'selected':'' }}>
                        {{ $name }}
                      </option>
                    @endforeach
                  </select>
                  <label for="department">Department *</label>
                  @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <select name="designation_id" id="designation"
                          class="form-select @error('designation_id') is-invalid @enderror" required>
                    <option value="" disabled {{ old('designation_id')?'':'selected' }}>Designation *</option>
                    @foreach($designations as $id => $name)
                      <option value="{{ $id }}" {{ old('designation_id')==$id?'selected':'' }}>
                        {{ $name }}
                      </option>
                    @endforeach
                  </select>
                  <label for="designation">Designation *</label>
                  @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <select name="schedule_id" id="schedule"
                          class="form-select @error('schedule_id') is-invalid @enderror">
                    <option value="">Schedule (optional)</option>
                    @foreach($schedules as $id => $name)
                      <option value="{{ $id }}" {{ old('schedule_id')==$id?'selected':'' }}>
                        {{ $name }}
                      </option>
                    @endforeach
                  </select>
                  <label for="schedule">Schedule</label>
                  @error('schedule_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <select name="employment_type" id="employment_type"
                          class="form-select @error('employment_type') is-invalid @enderror" required>
                    <option value="" disabled {{ old('employment_type')?'':'selected' }}>-- Employment Type * --</option>
                    @foreach($employmentTypes as $key => $label)
                      <option value="{{ $key }}" {{ old('employment_type')==$key?'selected':'' }}>
                        {{ $label }}
                      </option>
                    @endforeach
                  </select>
                  <label for="employment_type">Employment Type *</label>
                  @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="fingerprint_id" id="fingerprint_id"
                         class="form-control @error('fingerprint_id') is-invalid @enderror"
                         placeholder="Fingerprint ID" value="{{ old('fingerprint_id') }}">
                  <label for="fingerprint_id">Fingerprint ID</label>
                  @error('fingerprint_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>

            {{-- BENEFITS --}}
            <div class="tab-pane fade" id="benefits">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="text" name="gsis_id_no"
                         class="form-control @error('gsis_id_no') is-invalid @enderror"
                         placeholder="GSIS ID No." value="{{ old('gsis_id_no') }}">
                  <label>GSIS ID No.</label>
                  @error('gsis_id_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="pagibig_id_no"
                         class="form-control @error('pagibig_id_no') is-invalid @enderror"
                         placeholder="PAGIBIG ID No." value="{{ old('pagibig_id_no') }}">
                  <label>PAGIBIG ID No.</label>
                  @error('pagibig_id_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="philhealth_tin_id_no"
                         class="form-control @error('philhealth_tin_id_no') is-invalid @enderror"
                         placeholder="PHILHEALTH TIN ID No." value="{{ old('philhealth_tin_id_no') }}">
                  <label>PHILHEALTH TIN ID No.</label>
                  @error('philhealth_tin_id_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="sss_no"
                         class="form-control @error('sss_no') is-invalid @enderror"
                         placeholder="SSS No." value="{{ old('sss_no') }}">
                  <label>SSS No.</label>
                  @error('sss_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="tin_no"
                         class="form-control @error('tin_no') is-invalid @enderror"
                         placeholder="TIN No." value="{{ old('tin_no') }}">
                  <label>TIN No.</label>
                  @error('tin_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="agency_employee_no"
                         class="form-control @error('agency_employee_no') is-invalid @enderror"
                         placeholder="Agency Employee No." value="{{ old('agency_employee_no') }}">
                  <label>Agency Employee No.</label>
                  @error('agency_employee_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-success"><i class="bi bi-save2 me-1"></i>Save</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- View Employee Modal --}}
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-labelledby="viewEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-person-lines-fill me-2"></i> Employee Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row gx-4">
          {{-- Left Column --}}
          <div class="col-md-6">
            <dl class="row mb-4">
              <dt class="col-sm-4">Code</dt>
              <dd class="col-sm-8" id="view-employee_code"></dd>

              <dt class="col-sm-4">First Name</dt>
              <dd class="col-sm-8" id="view-first_name"></dd>

              <dt class="col-sm-4">Middle Name</dt>
              <dd class="col-sm-8" id="view-middle_name"></dd>

              <dt class="col-sm-4">Last Name</dt>
              <dd class="col-sm-8" id="view-last_name"></dd>

              <dt class="col-sm-4">Full Name</dt>
              <dd class="col-sm-8" id="view-full_name"></dd>

              <dt class="col-sm-4">Email</dt>
              <dd class="col-sm-8" id="view-email"></dd>

              <dt class="col-sm-4">Role</dt>
              <dd class="col-sm-8" id="view-role"></dd>

              <dt class="col-sm-4">Account Status</dt>
              <dd class="col-sm-8" id="view-account_status"></dd>

              <dt class="col-sm-4">Last Login</dt>
              <dd class="col-sm-8" id="view-last_login"></dd>

              <dt class="col-sm-4">Gender</dt>
              <dd class="col-sm-8" id="view-gender"></dd>

              <dt class="col-sm-4">DOB</dt>
              <dd class="col-sm-8" id="view-dob"></dd>

              <dt class="col-sm-4">Employment Type</dt>
              <dd class="col-sm-8" id="view-employment_type"></dd>

              <dt class="col-sm-4">Employment Status</dt>
              <dd class="col-sm-8" id="view-employment_status"></dd>
            </dl>
          </div>

          {{-- Right Column --}}
          <div class="col-md-6">
            <dl class="row mb-4">
              <dt class="col-sm-4">Start Date</dt>
              <dd class="col-sm-8" id="view-start_date"></dd>

              <dt class="col-sm-4">End Date</dt>
              <dd class="col-sm-8" id="view-end_date"></dd>

              <dt class="col-sm-4">Department</dt>
              <dd class="col-sm-8" id="view-department"></dd>

              <dt class="col-sm-4">Designation</dt>
              <dd class="col-sm-8" id="view-designation"></dd>

              <dt class="col-sm-4">Schedule</dt>
              <dd class="col-sm-8">
                <span id="view-schedule_name"></span><br>
                <small class="text-muted">
                  In: <span id="view-schedule_in"></span><br>
                  Out: <span id="view-schedule_out"></span>
                </small>
              </dd>

              <dt class="col-sm-4">Fingerprint ID</dt>
              <dd class="col-sm-8" id="view-fingerprint_id"></dd>

              <dt class="col-sm-4">Current Address</dt>
              <dd class="col-sm-8" id="view-current_address"></dd>

              <dt class="col-sm-4">Permanent Address</dt>
              <dd class="col-sm-8" id="view-permanent_address"></dd>

              <dt class="col-sm-4">Father Name</dt>
              <dd class="col-sm-8" id="view-father_name"></dd>

              <dt class="col-sm-4">Mother Name</dt>
              <dd class="col-sm-8" id="view-mother_name"></dd>

              <dt class="col-sm-4">Previous Company</dt>
              <dd class="col-sm-8" id="view-previous_company"></dd>

              <dt class="col-sm-4">Job Title</dt>
              <dd class="col-sm-8" id="view-job_title"></dd>

              <dt class="col-sm-4">Experience</dt>
              <dd class="col-sm-8" id="view-years_experience"></dd>

              <dt class="col-sm-4">Nationality</dt>
              <dd class="col-sm-8" id="view-nationality"></dd>

              <dt class="col-sm-4">GSIS ID No.</dt>
              <dd class="col-sm-8" id="view-gsis_id_no"></dd>

              <dt class="col-sm-4">Pag-IBIG ID No.</dt>
              <dd class="col-sm-8" id="view-pagibig_id_no"></dd>

              <dt class="col-sm-4">PhilHealth TIN No.</dt>
              <dd class="col-sm-8" id="view-philhealth_tin_id"></dd>

              <dt class="col-sm-4">SSS No.</dt>
              <dd class="col-sm-8" id="view-sss_no"></dd>

              <dt class="col-sm-4">TIN No.</dt>
              <dd class="col-sm-8" id="view-tin_no"></dd>

              <dt class="col-sm-4">Agency Emp No.</dt>
              <dd class="col-sm-8" id="view-agency_employee_no"></dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          Close
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const viewModalEl = document.getElementById('viewEmployeeModal');
    viewModalEl.addEventListener('show.bs.modal', event => {
      const btn  = event.relatedTarget;
      const data = JSON.parse(btn.getAttribute('data-employee'));

      // populate each field
      Object.entries(data).forEach(([key, val]) => {
        const el = viewModalEl.querySelector(`#view-${key}`);
        if (el) el.textContent = val ?? '—';
      });
    });
  });
</script>
@endpush