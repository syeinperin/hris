@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Designation</h2>
    <form action="{{ route('designations.update', $designation->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Designation Name</label>
            <input type="text" class="form-control" name="name" value="{{ $designation->name }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('designations.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
