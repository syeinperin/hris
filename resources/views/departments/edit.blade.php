@extends('layouts.app')

@section('content')
@php
    $departmentNames = \App\Models\Department::orderBy('name')
                         ->pluck('name')
                         ->toArray();
@endphp

<div class="container">
  <div class="card shadow-sm mb-4">
    <div class="card-header">
      <h3>Edit Department</h3>
    </div>
    <div class="card-body">
      <form action="{{ route('departments.update', $department) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
          <label for="name" class="form-label">Department Name *</label>
          <select name="name"
                  id="name"
                  class="form-select @error('name') is-invalid @enderror"
                  required>
            <option value="" disabled>Select a department…</option>
            @foreach($departmentNames as $name)
              <option value="{{ $name }}"
                {{ old('name', $department->name) === $name ? 'selected' : '' }}>
                {{ $name }}
              </option>
            @endforeach
          </select>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        <button type="submit" class="btn btn-success">
          Update
        </button>
        <a href="{{ route('departments.index') }}" class="btn btn-secondary">
          Cancel
        </a>
      </form>
    </div>
  </div>
</div>
@endsection
