@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Employee</h2>

    <!-- Display Error and Success Messages -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Edit Employee Form -->
    <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Profile Section -->
        <div class="card mb-3">
            <div class="card-header">Profile</div>
            <div class="card-body row">
                <div class="col-md-3">
                    <label>Profile Photo</label>
                    @if ($employee->profile_picture)
                        <div class="mb-2">
                            <img src="{{ asset($employee->profile_picture) }}" width="80" height="80" class="rounded-circle">
                        </div>
                    @endif
                    <input type="file" class="form-control" name="profile_picture" accept=".jpg, .jpeg, .png">
                </div>
                <div class="col-md-3">
                    <label>First Name</label>
                    <input type="text" class="form-control" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required>
                </div>
                <div class="col-md-3">
                    <label>Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}">
                </div>
                <div class="col-md-3">
                    <label>Last Name</label>
                    <input type="text" class="form-control" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required>
                </div>
            </div>
        </div>

        <!-- User Account Section -->
        <div class="card mb-3">
            <div class="card-header">User Account</div>
            <div class="card-body row">
                <div class="col-md-4">
                    <label>Email</label>
                    <input type="email" class="form-control" name="email" value="{{ old('email', $employee->user->email) }}" required>
                </div>
                <div class="col-md-4">
                    <label>Password <small>(Leave blank if not changing)</small></label>
                    <input type="password" class="form-control" name="password">
                </div>
                <div class="col-md-4">
                    <label>Role</label>
                    <select class="form-control" name="role" required>
                        @foreach (['admin','hr','employee','supervisor','timekeeper'] as $r)
                            <option value="{{ $r }}" {{ old('role', $employee->user->role->name) == $r ? 'selected' : '' }}>
                                {{ ucfirst($r) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Personal Details Section -->
        <div class="card mb-3">
            <div class="card-header">Personal Details</div>
            <div class="card-body row">
                <div class="col-md-3">
                    <label>Gender</label>
                    <select class="form-control" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Date of Birth</label>
                    <input type="date" class="form-control" name="dob" value="{{ old('dob', $employee->dob) }}" required>
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select class="form-control" name="status" required>
                        <option value="active" {{ old('status', $employee->user->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $employee->user->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Fingerprint ID</label>
                    <input type="text" class="form-control" name="fingerprint_id" value="{{ old('fingerprint_id', $employee->fingerprint_id) }}">
                </div>
            </div>
        </div>

        <!-- Address Section -->
        <div class="card mb-3">
            <div class="card-header">Address</div>
            <div class="card-body row">
                <div class="col-md-6">
                    <label>Current Address</label>
                    <input type="text" class="form-control" name="current_address" value="{{ old('current_address', $employee->current_address) }}" required>
                </div>
                <div class="col-md-6">
                    <label>Permanent Address</label>
                    <input type="text" class="form-control" name="permanent_address" value="{{ old('permanent_address', $employee->permanent_address) }}">
                </div>
            </div>
        </div>

        <!-- Family Section -->
        <div class="card mb-3">
            <div class="card-header">Family</div>
            <div class="card-body row">
                <div class="col-md-6">
                    <label>Father's Name</label>
                    <input type="text" class="form-control" name="father_name" value="{{ old('father_name', $employee->father_name) }}">
                </div>
                <div class="col-md-6">
                    <label>Mother's Name</label>
                    <input type="text" class="form-control" name="mother_name" value="{{ old('mother_name', $employee->mother_name) }}">
                </div>
            </div>
        </div>

        <!-- Experience Section -->
        <div class="card mb-3">
            <div class="card-header">Experience</div>
            <div class="card-body row">
                <div class="col-md-4">
                    <label>Previous Company</label>
                    <input type="text" class="form-control" name="previous_company" value="{{ old('previous_company', $employee->previous_company) }}">
                </div>
                <div class="col-md-4">
                    <label>Job Title</label>
                    <input type="text" class="form-control" name="job_title" value="{{ old('job_title', $employee->job_title) }}">
                </div>
                <div class="col-md-4">
                    <label>Years of Experience</label>
                    <input type="number" class="form-control" name="years_experience" value="{{ old('years_experience', $employee->years_experience) }}">
                </div>
            </div>
        </div>

        <!-- Work Details Section -->
        <div class="card mb-3">
            <div class="card-header">Work Details</div>
            <div class="card-body row">
                <div class="col-md-6">
                    <label>Department</label>
                    <select name="department_id" class="form-control" required>
                        <option value="">Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Designation</label>
                    <select name="designation_id" class="form-control" required>
                        <option value="">Select Designation</option>
                        @foreach ($designations as $designation)
                            <option value="{{ $designation->id }}" {{ old('designation_id', $employee->designation_id) == $designation->id ? 'selected' : '' }}>
                                {{ $designation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Schedule Section -->
        <div class="card mb-3">
            <div class="card-header">Schedule (Shift)</div>
            <div class="card-body">
                <select name="schedule_id" class="form-control">
                    <option value="">Select Schedule</option>
                    @foreach ($schedules as $schedule)
                        <option value="{{ $schedule->id }}" {{ old('schedule_id', $employee->schedule_id) == $schedule->id ? 'selected' : '' }}>
                            {{ $schedule->name }} ({{ $schedule->time_in }} - {{ $schedule->time_out }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Save / Cancel -->
        <button type="submit" class="btn btn-primary">Update Employee</button>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
