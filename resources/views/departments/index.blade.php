@extends('layouts.app')

@section('content')
@php
    // get all seeded department names
    $departmentNames = \App\Models\Department::orderBy('name')->pluck('name')->toArray();
@endphp

<div class="container">
  <h2 class="mb-4">Departments</h2>

  {{-- Search Bar --}}
  <x-search-bar
      :action="route('departments.index')"
      placeholder="Search Departments..."
      :filters="[]"
  />

  {{-- Add Department Button --}}
  <button class="btn btn-primary mb-3"
          data-bs-toggle="modal"
          data-bs-target="#addDepartmentModal">
    Add Department
  </button>

  {{-- Departments Table --}}
  <table class="table table-striped">
    <thead>
      <tr><th>Name</th><th>Actions</th></tr>
    </thead>
    <tbody>
      @foreach ($departments as $department)
        <tr>
          <td>{{ $department->name }}</td>
          <td>
            {{-- Edit button opens modal --}}
            <button class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editDepartmentModal{{ $department->id }}">
              Edit
            </button>

            {{-- Delete form --}}
            <form action="{{ route('departments.destroy', $department) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('Are you sure?')">
              @csrf
              @method('DELETE')
              <button class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>

        {{-- Edit Department Modal --}}
        <div class="modal fade"
             id="editDepartmentModal{{ $department->id }}"
             tabindex="-1"
             aria-labelledby="editDepartmentLabel{{ $department->id }}"
             aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="{{ route('departments.update', $department) }}"
                    method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                  <h5 class="modal-title"
                      id="editDepartmentLabel{{ $department->id }}">
                    Edit Department
                  </h5>
                  <button type="button"
                          class="btn-close"
                          data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                  <div class="mb-3">
                    <label for="name-{{ $department->id }}" class="form-label">
                      Department Name *
                    </label>
                    <select name="name"
                            id="name-{{ $department->id }}"
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
                </div>

                <div class="modal-footer">
                  <button type="submit" class="btn btn-success">
                    Update
                  </button>
                  <button type="button"
                          class="btn btn-secondary"
                          data-bs-dismiss="modal">
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </tbody>
  </table>

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $departments->links('pagination::bootstrap-5') }}
  </div>
</div>

{{-- Add Department Modal --}}
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('departments.store') }}" method="POST">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Add Department</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="name-add" class="form-label">Department Name *</label>
            <select name="name"
                    id="name-add"
                    class="form-select @error('name') is-invalid @enderror"
                    required>
              <option value="" disabled selected>Select a department…</option>
              @foreach($departmentNames as $name)
                <option value="{{ $name }}">{{ $name }}</option>
              @endforeach
            </select>
            @error('name')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Department</button>
          <button type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
