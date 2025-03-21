@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add New Designation</h2>
    <form action="{{ route('designations.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Designation Name</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        <button type="submit" class="btn btn-success">Save</button>
        <a href="{{ route('designations.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
