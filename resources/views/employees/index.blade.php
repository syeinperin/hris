@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Employee Management</h2>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">Add Employee</button>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Profile</th>
                <th>Name</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($employees as $employee)
            <tr>
                <td>
                    @if ($employee->profile_picture)
                        <img src="{{ asset($employee->profile_picture) }}" width="50" height="50" class="rounded-circle">
                    @else
                        <span>No Image</span>
                    @endif
                </td>
                <td>{{ $employee->name }}</td>
                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                <td>{{ $employee->designation->name ?? 'N/A' }}</td>
                <td>{{ ucfirst($employee->user->status ?? 'N/A') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-danger">No employee records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- ✅ FIXED: Ensure scrollability -->
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                    {{-- your entire existing content goes here, unchanged --}}
                    <!-- Profile Info -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Profile Picture</label>
                            <input type="file" class="form-control" name="profile_picture">
                        </div>
                        <div class="col-md-3">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>

                    <!-- Contact & Account -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="hr">HR</option>
                                <option value="employee">Employee</option>
                                <option value="supervisor">Supervisor</option>
                                <option value="timekeeper">Timekeeper</option>
                            </select>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="">Select</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Current Address</label>
                            <input type="text" name="current_address" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Permanent Address</label>
                            <input type="text" name="permanent_address" class="form-control">
                        </div>
                    </div>

                    <!-- Family & Experience -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Father's Name</label>
                            <input type="text" name="father_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Mother's Name</label>
                            <input type="text" name="mother_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Previous Company</label>
                            <input type="text" name="previous_company" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Job Title</label>
                            <input type="text" name="job_title" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Years of Experience</label>
                            <input type="number" name="years_experience" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Nationality</label>
                            <input type="text" name="nationality" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Fingerprint ID</label>
                            <input type="text" name="fingerprint_id" class="form-control">
                        </div>
                    </div>

                    <!-- Work -->
                    <div class="row mb-3">
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

                </div> <!-- /modal-body -->

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Employee</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle if not already loaded -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
