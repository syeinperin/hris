@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Pending User Accounts</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($users->count() > 0)
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role ID</th>
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
                    <td>{{ $user->role_id }}</td>
                    <td><span class="badge bg-warning">{{ $user->status }}</span></td>
                    <td>
                        <!-- Approve user -->
                        <form action="{{ route('users.approve', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm btn-success">
                                Approve
                            </button>
                        </form>

                        <!-- Or, if we want to delete the user altogether -->
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;" 
                              onsubmit="return confirm('Are you sure you want to delete this pending user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    @else
        <p class="mt-4 text-muted">No pending users found.</p>
    @endif
</div>
@endsection
