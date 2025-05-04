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

  <form action="{{ route('employees.update',$employee) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    {{-- Show Code (read-only) --}}
    <div class="mb-3">
      <label class="form-label">Employee Code</label>
      <input type="text" class="form-control" value="{{ $employee->employee_code }}" readonly>
    </div>

    {{-- ACCOUNT --}}
    <div class="card mb-4">
      <div class="card-header">Account</div>
      <div class="card-body row g-3">
        <div class="col-md-4 form-floating">
          <input type="email" name="email"
                 class="form-control" id="email" placeholder="Email *"
                 value="{{ old('email',$employee->user->email) }}" required>
          <label for="email">Email *</label>
        </div>
        <div class="col-md-4 form-floating">
          <select name="role" class="form-select" id="role" required>
            <option value="" disabled>Select role…</option>
            @foreach($roles as $r)
              <option value="{{ $r }}" {{ old('role',$employee->user->role->name)==$r?'selected':'' }}>
                {{ ucfirst($r) }}
              </option>
            @endforeach
          </select>
          <label for="role">Role *</label>
        </div>
        <div class="col-md-4 form-floating">
          <select name="status" class="form-select" id="status" required>
            <option value="active"   {{ old('status',$employee->user->status)=='active'   ?'selected':'' }}>Active</option>
            <option value="inactive" {{ old('status',$employee->user->status)=='inactive' ?'selected':'' }}>Inactive</option>
            <option value="pending"  {{ old('status',$employee->user->status)=='pending'  ?'selected':'' }}>Pending</option>
          </select>
          <label for="status">Status *</label>
        </div>
        <div class="col-md-4 form-floating">
          <input type="password" name="password"
                 class="form-control" id="password" placeholder="New Password (optional)">
          <label for="password">New Password</label>
        </div>
        <div class="col-md-4 form-floating">
          <input type="password" name="password_confirmation"
                 class="form-control" id="password_confirmation" placeholder="Confirm">
          <label for="password_confirmation">Confirm</label>
        </div>
      </div>
    </div>

    {{-- PERSONAL --}}
    <div class="card mb-4">
      <div class="card-header">Personal</div>
      <div class="card-body row g-3">
        <div class="col-md-4 form-floating">
          <input type="text" name="first_name"
                 class="form-control" id="first_name" placeholder="First Name *"
                 value="{{ old('first_name',$employee->first_name) }}" required>
          <label for="first_name">First Name *</label>
        </div>
        <div class="col-md-4 form-floating">
          <input type="text" name="middle_name"
                 class="form-control" id="middle_name" placeholder="Middle Name"
                 value="{{ old('middle_name',$employee->middle_name) }}">
          <label for="middle_name">Middle Name</label>
        </div>
        <div class="col-md-4 form-floating">
          <input type="text" name="last_name"
                 class="form-control" id="last_name" placeholder="Last Name *"
                 value="{{ old('last_name',$employee->last_name) }}" required>
          <label for="last_name">Last Name *</label>
        </div>
        <div class="col-md-4 form-floating">
          <select name="gender" class="form-select" id="gender" required>
            <option value="male"   {{ old('gender',$employee->gender)=='male'   ?'selected':'' }}>Male</option>
            <option value="female" {{ old('gender',$employee->gender)=='female' ?'selected':'' }}>Female</option>
            <option value="other"  {{ old('gender',$employee->gender)=='other'  ?'selected':'' }}>Other</option>
          </select>
          <label for="gender">Gender *</label>
        </div>
        <div class="col-md-4 form-floating">
          <input type="date" name="dob"
                 class="form-control" id="dob" placeholder="Date of Birth *"
                 value="{{ old('dob',$employee->dob->toDateString()) }}" required>
          <label for="dob">Date of Birth *</label>
        </div>
        <div class="col-md-4">
          <label class="form-label">Profile Picture</label>
          <input type="file" name="profile_picture" class="form-control" accept="image/*">
        </div>
      </div>
    </div>

    {{-- WORK --}}
    <div class="card mb-4">
      <div class="card-header">Work</div>
      <div class="card-body row g-3">
        <div class="col-md-4 form-floating">
          <select name="department_id" class="form-select" id="department" required>
            <option value="">Department *</option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}" {{ old('department_id',$employee->department_id)==$d->id?'selected':'' }}>
                {{ $d->name }}
              </option>
            @endforeach
          </select>
          <label for="department">Department *</label>
        </div>
        <div class="col-md-4 form-floating">
          <select name="designation_id" class="form-select" id="designation" required>
            <option value="">Designation *</option>
            @foreach($designations as $d)
              <option value="{{ $d->id }}" {{ old('designation_id',$employee->designation_id)==$d->id?'selected':'' }}>
                {{ $d->name }}
              </option>
            @endforeach
          </select>
          <label for="designation">Designation *</label>
        </div>
        <div class="col-md-4 form-floating">
          <select name="schedule_id" class="form-select" id="schedule">
            <option value="">Schedule (optional)</option>
            @foreach($schedules as $s)
              <option value="{{ $s->id }}" {{ old('schedule_id',$employee->schedule_id)==$s->id?'selected':'' }}>
                {{ $s->name }}
              </option>
            @endforeach
          </select>
          <label for="schedule">Schedule</label>
        </div>
        <div class="col-md-4 form-floating">
          <input type="text" name="fingerprint_id"
                 class="form-control" id="fingerprint_id" placeholder="Fingerprint ID"
                 value="{{ old('fingerprint_id',$employee->fingerprint_id) }}">
          <label for="fingerprint_id">Fingerprint ID</label>
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
