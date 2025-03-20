@extends('layouts.app')

@section('content')
    <h2>Employees</h2>
    <a href="{{ route('employees.create') }}" class="btn btn-primary">Add Employee</a>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                <tr>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->department }}</td> <!-- Removed duplicate column for position -->
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
        </tbody>
    </table>
@endsection
