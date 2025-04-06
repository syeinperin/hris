@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Employees</h2>

    <!-- Filter & Search Bar -->
    <form action="{{ route('employees.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="department_id" class="form-control">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-5">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name or email...">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary w-100">Reset</a>
        </div>
    </form>

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
                <th>Email</th>
                <th>Department</th>
                <th>Actions</th>
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
                    <td>{{ $employee->email }}</td>
                    <td>{{ $employee->department->name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" style="display:inline-block">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-danger">No employee records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
