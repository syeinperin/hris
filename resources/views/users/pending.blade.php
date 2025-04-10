@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Pending User Approvals</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($users->count() > 0)
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-warning">{{ $user->status }}</span>
                    </td>
                    <td>
                        <!-- Approve Button -->
                        <form action="{{ route('users.approve', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <!-- Delete Button -->
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    @else
        <p>No pending users found.</p>
    @endif
</div>
@endsection
