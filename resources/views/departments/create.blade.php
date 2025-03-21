@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Department</h2>
    <form action="{{ route('departments.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Department Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Department</button>
    </form>
</div>
@endsection
