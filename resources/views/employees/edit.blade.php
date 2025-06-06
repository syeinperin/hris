@extends('layouts.app')

@section('page_title','Edit Employee')

@section('content')
<div class="container">
  <h2 class="mb-4">Edit {{ $employee->employee_code }}</h2>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('employees.update', $employee) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Employee Code (read‐only) --}}
    <div class="mb-3">
      <label class="form-label">Employee Code</label>
      <input type="text"
             class="form-control"
             value="{{ $employee->employee_code }}"
             readonly>
    </div>

    {{-- ACCOUNT --}}
    <div class="card mb-4">
      <div class="card-header">Account</div>
      <div class="card-body row g-3">
        <div class="col-md-4 form-floating">
          <input type="email"
                 name="email"
                 id="email"
                 class="form-control @error('email') is-invalid @enderror"
                 placeholder="Email *"
                 value="{{ old('email', $employee->user->email) }}"
                 required>
          <label for="email">Email *</label>
          @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <select name="role"
                  id="role"
                  class="form-select @error('role') is-invalid @enderror"
                  required>
            <option value="" disabled>Select role…</option>
            @foreach($roles as $r)
              <option value="{{ $r }}"
                {{ old('role', $employee->user->role->name) == $r ? 'selected' : '' }}>
                {{ ucfirst($r) }}
              </option>
            @endforeach
          </select>
          <label for="role">Role *</label>
          @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <select name="status"
                  id="status"
                  class="form-select @error('status') is-invalid @enderror"
                  required>
            <option value="active"
              {{ old('status', $employee->user->status) == 'active' ? 'selected' : '' }}>
              Active
            </option>
            <option value="inactive"
              {{ old('status', $employee->user->status) == 'inactive' ? 'selected' : '' }}>
              Inactive
            </option>
            <option value="pending"
              {{ old('status', $employee->user->status) == 'pending' ? 'selected' : '' }}>
              Pending
            </option>
          </select>
          <label for="status">Status *</label>
          @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <input type="password"
                 name="password"
                 id="password"
                 class="form-control @error('password') is-invalid @enderror"
                 placeholder="New Password (optional)">
          <label for="password">New Password</label>
          @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <input type="password"
                 name="password_confirmation"
                 id="password_confirmation"
                 class="form-control"
                 placeholder="Confirm">
          <label for="password_confirmation">Confirm</label>
        </div>
      </div>
    </div>

    {{-- PERSONAL --}}
    <div class="card mb-4">
      <div class="card-header">Personal</div>
      <div class="card-body row g-3">
        <div class="col-md-4 form-floating">
          <input type="text"
                 name="first_name"
                 id="first_name"
                 class="form-control @error('first_name') is-invalid @enderror"
                 placeholder="First Name *"
                 value="{{ old('first_name', $employee->first_name) }}"
                 required>
          <label for="first_name">First Name *</label>
          @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <input type="text"
                 name="middle_name"
                 id="middle_name"
                 class="form-control @error('middle_name') is-invalid @enderror"
                 placeholder="Middle Name"
                 value="{{ old('middle_name', $employee->middle_name) }}">
          <label for="middle_name">Middle Name</label>
          @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <input type="text"
                 name="last_name"
                 id="last_name"
                 class="form-control @error('last_name') is-invalid @enderror"
                 placeholder="Last Name *"
                 value="{{ old('last_name', $employee->last_name) }}"
                 required>
          <label for="last_name">Last Name *</label>
          @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <select name="gender"
                  id="gender"
                  class="form-select @error('gender') is-invalid @enderror"
                  required>
            <option value="male"
              {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>
              Male
            </option>
            <option value="female"
              {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>
              Female
            </option>
            <option value="other"
              {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>
              Other
            </option>
          </select>
          <label for="gender">Gender *</label>
          @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <input type="date"
                 name="dob"
                 id="dob"
                 class="form-control @error('dob') is-invalid @enderror"
                 placeholder="Date of Birth *"
                 value="{{ old('dob', $employee->dob?->format('Y-m-d')) }}"
                 required>
          <label for="dob">Date of Birth *</label>
          @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6 form-floating">
          <input type="text"
                 name="current_address"
                 id="current_address"
                 class="form-control @error('current_address') is-invalid @enderror"
                 placeholder="Current Address *"
                 value="{{ old('current_address', $employee->current_address) }}"
                 required>
          <label for="current_address">Current Address *</label>
          @error('current_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6 form-floating">
          <input type="text"
                 name="permanent_address"
                 id="permanent_address"
                 class="form-control @error('permanent_address') is-invalid @enderror"
                 placeholder="Permanent Address"
                 value="{{ old('permanent_address', $employee->permanent_address) }}">
          <label for="permanent_address">Permanent Address</label>
          @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
          <label class="form-label">Profile Picture</label>
          <input type="file"
                 name="profile_picture"
                 class="form-control @error('profile_picture') is-invalid @enderror"
                 accept="image/*">
          @error('profile_picture')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>

    {{-- WORK --}}
    <div class="card mb-4">
      <div class="card-header">Work</div>
      <div class="card-body row g-3">
        <div class="col-md-4 form-floating">
          <select name="department_id"
                  id="department"
                  class="form-select @error('department_id') is-invalid @enderror"
                  required>
            <option value="" disabled {{ old('department_id', $employee->department_id) ? '' : 'selected' }}>
              Department *
            </option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}"
                {{ old('department_id', $employee->department_id) == $d->id ? 'selected' : '' }}>
                {{ $d->name }}
              </option>
            @endforeach
          </select>
          <label for="department">Department *</label>
          @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <select name="designation_id"
                  id="designation"
                  class="form-select @error('designation_id') is-invalid @enderror"
                  required>
            <option value="" disabled {{ old('designation_id', $employee->designation_id) ? '' : 'selected' }}>
              Designation *
            </option>
            @foreach($designations as $d)
              <option value="{{ $d->id }}"
                {{ old('designation_id', $employee->designation_id) == $d->id ? 'selected' : '' }}>
                {{ $d->name }}
              </option>
            @endforeach
          </select>
          <label for="designation">Designation *</label>
          @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <select name="schedule_id"
                  id="schedule"
                  class="form-select @error('schedule_id') is-invalid @enderror">
            <option value="">Schedule (optional)</option>
            @foreach($schedules as $s)
              <option value="{{ $s->id }}"
                {{ old('schedule_id', $employee->schedule_id) == $s->id ? 'selected' : '' }}>
                {{ $s->name }}
              </option>
            @endforeach
          </select>
          <label for="schedule">Schedule</label>
          @error('schedule_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Employment Type --}}
        <div class="col-md-4 form-floating">
          <select name="employment_type"
                  id="employment_type"
                  class="form-select @error('employment_type') is-invalid @enderror"
                  required>
            @foreach($employmentTypes as $key => $label)
              <option value="{{ $key }}"
                {{ old('employment_type', $employee->employment_type) == $key ? 'selected' : '' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>
          <label for="employment_type">Employment Type *</label>
          @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Contract End Date --}}
        <div class="col-md-4 form-floating">
          <input type="date"
                 name="employment_end_date"
                 id="employment_end_date"
                 class="form-control @error('employment_end_date') is-invalid @enderror"
                 placeholder="Contract End Date *"
                 value="{{ old('employment_end_date', $employee->employment_end_date?->format('Y-m-d')) }}"
                 required
          >
          <label for="employment_end_date">Contract End Date *</label>
          @error('employment_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 form-floating">
          <input type="text"
                 name="fingerprint_id"
                 id="fingerprint_id"
                 class="form-control @error('fingerprint_id') is-invalid @enderror"
                 placeholder="Fingerprint ID"
                 value="{{ old('fingerprint_id', $employee->fingerprint_id) }}"
          >
          <label for="fingerprint_id">Fingerprint ID</label>
          @error('fingerprint_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>

    {{-- Submit Buttons --}}
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-save2 me-1"></i>Update
      </button>
      <a href="{{ route('employees.index') }}" class="btn btn-secondary">
        Cancel
      </a>
    </div>
  </form>
</div>
@endsection
