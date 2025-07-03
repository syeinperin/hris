@extends('layouts.app')
@section('page_title','Edit Leave Type')

@section('content')
<div class="container">
  <h3>Edit Leave Type</h3>
  <form action="{{ route('leave-types.update',$leaveType) }}" method="POST">
    @csrf @method('PUT')

    <div class="mb-3">
      <label>Name</label>
      <input name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name',$leaveType->name) }}"
             required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label>Default Days</label>
      <input type="number"
             name="default_days"
             min="0"
             class="form-control @error('default_days') is-invalid @enderror"
             value="{{ old('default_days',$leaveType->default_days) }}"
             required>
      @error('default_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control">{{ old('description',$leaveType->description) }}</textarea>
    </div>

    <div class="form-check mb-3">
      <input name="is_active"
             type="checkbox"
             class="form-check-input"
             id="is_active"
             {{ old('is_active',$leaveType->is_active) ? 'checked':'' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>

    <button class="btn btn-success">Update</button>
    <a href="{{ route('leave-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
@endsection
