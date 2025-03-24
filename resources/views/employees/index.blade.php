@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Employees</h2>

    <!-- Button to Open Modal -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
        Add Employee
    </button>

    <!-- Employee Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Profile</th> <!-- New column for profile picture -->
                <th>Name</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody> <!-- Keep only one <tbody> -->
            @if($employees->count() > 0)
                @foreach ($employees as $employee)
                    <tr>
                        <td>
                            @if($employee->profile_picture)
                                <img src="{{ asset($employee->profile_picture) }}" alt="Profile Photo" width="50" height="50" class="rounded-circle">
                            @else
                                <span>No Image</span>
                            @endif
                        </td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->department->name ?? 'N/A' }}</td> <!-- Ensure department exists -->
                        <td>
                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" style="display:inline;">
                                @csrf 
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center text-danger">No employees found.</td>
                </tr>
            @endif
        </tbody> <!-- Ensure only one closing </tbody> -->
    </table>
</div>

<!-- Bootstrap Modal for Adding Employee -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Scrollable Modal Body -->
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    @include('employees.create') <!-- Load create form dynamically -->
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Employee</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ensure Bootstrap JS is Loaded -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

@endsection
