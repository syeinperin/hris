@extends('layouts.app')

@section('page_title','Inactive Employees')

@section('content')
<div class="container-fluid">
  {{-- Header --}}
  <div class="row mb-4">
    <div class="col">
      <h3><i class="bi-person-x me-1"></i> Inactive Employees</h3>
    </div>
    <div class="col text-end">
      <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
        ← Active Employees
      </a>
    </div>
  </div>

  {{-- Filters --}}
  <form method="GET" action="{{ route('employees.inactive') }}" class="row g-2 mb-4">
    <div class="col-md-4">
      <select name="department_id" class="form-select">
        <option value="">All Departments</option>
        @foreach($departments as $id => $name)
          <option value="{{ $id }}" @selected(request('department_id')==$id)>{{ $name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <select name="employment_type" class="form-select">
        @foreach($employmentTypes as $key => $label)
          <option value="{{ $key }}" @selected(request('employment_type')===$key)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4 d-flex">
      <button class="btn btn-primary me-2" type="submit">Filter</button>
      <a href="{{ route('employees.inactive') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th>
          <th>Code</th>
          <th>Name</th>
          <th>Email</th>
          <th>Dept</th>
          <th>Type</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Schedule</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($employees as $emp)
          <tr>
            <td>{{ $emp->id }}</td>
            <td>{{ $emp->employee_code }}</td>
            <td>{{ $emp->name }}</td>
            <td>{{ $emp->email }}</td>
            <td>{{ $emp->department->name }}</td>
            <td>{{ ucfirst($emp->employment_type) }}</td>
            <td>{{ optional($emp->employment_start_date)->toDateString() }}</td>
            <td>{{ optional($emp->employment_end_date)->toDateString() }}</td>
            <td>{{ $emp->schedule?->name }}</td>
            <td class="text-nowrap">
              <form
                method="POST"
                action="{{ route('employees.restore', $emp) }}"
                class="d-inline"
                onsubmit="return confirm('Restore {{ $emp->employee_code }} to active?');"
              >
                @csrf
                @method('PATCH')
                <button class="btn btn-sm btn-success">Restore</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="text-center">No inactive employees.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-3">{{ $employees->links() }}</div>
</div>
@endsection
