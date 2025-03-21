@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Department</h2>
    <form action="{{ route('departments.update', $department->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Department Name</label>
            <input type="text" class="form-control" name="name" value="{{ $department->name }}" required>
        </div>
        <button type="submit" class="btn btn-success">Update Department</button>
    </form>
</div>
@endsection
