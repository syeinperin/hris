@extends('layouts.app')
@section('page_title','Add Leave Type')

@section('content')
<div class="container">
  <h3>Add Leave Type</h3>
  <form action="{{ route('leave-types.store') }}" method="POST">
    @csrf

    <div class="mb-3">
      <label>Name</label>
      <input name="name"
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name') }}" required>
      @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label>Default Days</label>
      <input type="number"
             name="default_days"
             min="0"
             class="form-control @error('default_days') is-invalid @enderror"
             value="{{ old('default_days',0) }}"
             required>
      @error('default_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" class="form-control">{{ old('description') }}</textarea>
    </div>

    <div class="form-check mb-3">
      <input name="is_active"
             type="checkbox"
             class="form-check-input"
             id="is_active"
             {{ old('is_active',true) ? 'checked':'' }}>
      <label class="form-check-label" for="is_active">Active</label>
    </div>

    <button class="btn btn-success">Save</button>
    <a href="{{ route('leave-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
@endsection
