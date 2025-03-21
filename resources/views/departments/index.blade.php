@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Departments</h2>
    <a href="{{ route('departments.create') }}" class="btn btn-primary">Add Department</a>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departments as $department)
                <tr>
                    <td>{{ $department->name }}</td>
                    <td>
                        <a href="{{ route('departments.edit', $department->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('departments.destroy', $department->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
