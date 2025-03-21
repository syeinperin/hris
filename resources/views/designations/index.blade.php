@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Designations</h2>
    <a href="{{ route('designations.create') }}" class="btn btn-primary">Add Designation</a>

    <form action="{{ route('designations.index') }}" method="GET" class="mt-3">
        <input type="text" name="search" placeholder="Search Designations..." class="form-control w-50 d-inline">
        <button type="submit" class="btn btn-secondary">Search</button>
    </form>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($designations as $designation)
                <tr>
                    <td>{{ $designation->name }}</td>
                    <td>
                        <a href="{{ route('designations.edit', $designation->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('designations.destroy', $designation->id) }}" method="POST" style="display:inline;">
                            @csrf 
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    {{ $designations->links() }}
</div>
@endsection
