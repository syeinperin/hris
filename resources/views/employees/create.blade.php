@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Employee</h2>
    <form action="{{ route('employees.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label class="form-label">User</label>
        <select name="user_id" class="form-control" required>
            <option value="">Select User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required>
    </div>

    <div class="mb-3">
    <label class="form-label">Department</label>
    <select name="department" class="form-control" required>
        <option value="">Select Department</option>
        <option value="Office">Office</option>
        <option value="Production">Production</option>
        <option value="Spinning">Spinning</option>
        <option value="Weaving">Weaving</option>
        <option value="Finishing">Finishing</option>
        <option value="Inspection">Inspection</option>
        <option value="Packing">Packing</option>
        <option value="Engineering">Engineering</option>
    </select>
</div>

    <button type="submit" class="btn btn-primary">Save Employee</button>
</form>
@endsection
