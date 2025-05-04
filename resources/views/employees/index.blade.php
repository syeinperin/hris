@extends('layouts.app')

@section('page_title','Employees')

@section('content')
<div class="container">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i>Employees</h4>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
        <i class="bi bi-plus-lg"></i> Add
      </button>
    </div>

    <div class="card-body">
      {{-- Filters --}}
      <form action="{{ route('employees.index') }}" method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
          <select name="department_id" class="form-select">
            <option value="">All Departments</option>
            @foreach($departments as $id => $name)
              <option value="{{ $id }}" {{ request('department_id')==$id?'selected':'' }}>
                {{ $name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-5">
          <input type="text" name="search"
                 class="form-control"
                 placeholder="Search name, code or email…"
                 value="{{ request('search') }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
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
              <th>Profile</th>
              <th>Name</th>
              <th>Email</th>
              <th>Dept</th>
              <th>Schedule</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($employees as $e)
              <tr>
                <td>{{ $e->id }}</td>
                <td>{{ $e->employee_code }}</td>
                <td class="text-center">
                  @if($e->profile_picture)
                    <img src="{{ asset($e->profile_picture) }}"
                         alt="Profile" class="rounded-circle" width="40" height="40">
                  @else
                    <span class="text-muted small">No Image</span>
                  @endif
                </td>
                <td>{{ $e->name }}</td>
                <td>{{ $e->user->email }}</td>
                <td>{{ $e->department->name ?? '—' }}</td>
                <td>
                  @if($e->schedule)
                    <strong>{{ $e->schedule->name }}</strong><br>
                    <small class="text-muted">
                      {{ $e->schedule->time_in }}–{{ $e->schedule->time_out }}
                    </small>
                  @else
                    <span class="text-muted">N/A</span>
                  @endif
                </td>
                <td class="text-center">
                  <a href="{{ route('employees.edit',$e) }}" class="btn btn-sm btn-warning me-1">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form action="{{ route('employees.destroy',$e) }}"
                        method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this employee?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
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

          <ul class="nav nav-tabs mb-4" id="empTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#account">Account</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#personal">Personal</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#work">Work</button></li>
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
                      <option value="{{ $r }}" {{ old('role')==$r?'selected':'' }}>{{ ucfirst($r) }}</option>
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
                    <option value="male"   {{ old('gender')=='male'   ?'selected':'' }}>Male</option>
                    <option value="female" {{ old('gender')=='female' ?'selected':'' }}>Female</option>
                    <option value="other"  {{ old('gender')=='other'  ?'selected':'' }}>Other</option>
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
                      <option value="{{ $id }}" {{ old('department_id')==$id?'selected':'' }}>{{ $name }}</option>
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
                      <option value="{{ $id }}" {{ old('designation_id')==$id?'selected':'' }}>{{ $name }}</option>
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
                      <option value="{{ $id }}" {{ old('schedule_id')==$id?'selected':'' }}>{{ $name }}</option>
                    @endforeach
                  </select>
                  <label for="schedule">Schedule</label>
                  @error('schedule_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save2 me-1"></i>Save
          </button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection