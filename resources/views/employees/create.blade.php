@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Employees</h2>

    <!-- Success/Error Messages -->
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
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Add Employee Button -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
        Add Employee
    </button>

    <!-- Employee Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Profile</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employees as $employee)
                <tr>
                    <td>
                        @if($employee->profile_picture)
                            <img src="{{ asset($employee->profile_picture) }}" alt="Profile Photo" width="50" height="50" class="rounded-circle">
                        @else
                            <span>No Image</span>
                        @endif
                    </td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" style="display:inline;">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-danger">No employees found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="card mb-3">
                        <div class="card-header">Profile</div>
                        <div class="card-body row">
                            <div class="col-md-3">
                                <label>Profile Photo</label>
                                <input type="file" class="form-control" name="profile_picture" accept=".jpg, .jpeg, .png">
                            </div>
                            <div class="col-md-3">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-3">
                                <label>Middle Name</label>
                                <input type="text" class="form-control" name="middle_name">
                            </div>
                            <div class="col-md-3">
                                <label>Last Name</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">User Account</div>
                        <div class="card-body row">
                            <div class="col-md-4">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-4">
                                <label>Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-md-4">
                                <label>Role</label>
                                <select class="form-control" name="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="hr">HR</option>
                                    <option value="employee">Employee</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="timekeeper">Timekeeper</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">Personal Details</div>
                        <div class="card-body row">
                            <div class="col-md-3">
                                <label>Gender</label>
                                <select class="form-control" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Date of Birth</label>
                                <input type="date" class="form-control" name="dob" required>
                            </div>
                            <div class="col-md-3">
                                <label>Status</label>
                                <select class="form-control" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Fingerprint ID</label>
                                <input type="text" class="form-control" name="fingerprint_id">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">Address</div>
                        <div class="card-body row">
                            <div class="col-md-6">
                                <label>Current Address</label>
                                <input type="text" class="form-control" name="current_address" required>
                            </div>
                            <div class="col-md-6">
                                <label>Permanent Address</label>
                                <input type="text" class="form-control" name="permanent_address">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">Family</div>
                        <div class="card-body row">
                            <div class="col-md-6">
                                <label>Father's Name</label>
                                <input type="text" class="form-control" name="father_name">
                            </div>
                            <div class="col-md-6">
                                <label>Mother's Name</label>
                                <input type="text" class="form-control" name="mother_name">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">Experience</div>
                        <div class="card-body row">
                            <div class="col-md-4">
                                <label>Previous Company</label>
                                <input type="text" class="form-control" name="previous_company">
                            </div>
                            <div class="col-md-4">
                                <label>Job Title</label>
                                <input type="text" class="form-control" name="job_title">
                            </div>
                            <div class="col-md-4">
                                <label>Years of Experience</label>
                                <input type="number" class="form-control" name="years_experience">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">Work Details</div>
                        <div class="card-body row">
                            <div class="col-md-6">
                                <label>Department</label>
                                <select name="department_id" class="form-control" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Designation</label>
                                <select name="designation_id" class="form-control" required>
                                    <option value="">Select Designation</option>
                                    @foreach ($designations as $designation)
                                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save/Cancel Footer -->
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Employee</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection
