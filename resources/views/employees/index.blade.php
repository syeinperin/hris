{{-- resources/views/employees/index.blade.php --}}
@extends('layouts.app')

@push('scripts')
  <script src="{{ asset('js/ph-location.js') }}"></script>
@endpush

@section('page_title', 'Employees')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i> Employees</h4>
      <div class="d-flex align-items-center">
        <a href="{{ route('employees.endings') }}" class="btn btn-outline-warning btn-sm me-2">
          <i class="bi bi-exclamation-triangle me-1"></i> Ending Soon
          <span class="badge bg-warning text-dark">{{ $endingCount }}</span>
        </a>
        <a href="{{ route('employees.inactive') }}" class="btn btn-outline-secondary btn-sm me-2">
          <i class="bi bi-person-x me-1"></i> Inactive
          <span class="badge bg-secondary">{{ $inactiveCount }}</span>
        </a>
        <a href="{{ route('departments.index') }}" class="btn btn-outline-primary btn-sm me-2">
          <i class="bi bi-building me-1"></i> Departments
        </a>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
          <i class="bi bi-plus-lg me-1"></i> Add
        </button>
      </div>
    </div>

    <div class="card-body">
      {{-- Filters --}}
      <form method="GET" action="{{ route('employees.index') }}" class="row g-3 mb-4">
        <div class="col-md-3">
          <select name="department_id" class="form-select">
            <option value="">All Departments</option>
            @foreach($departments as $id => $name)
              <option value="{{ $id }}" {{ request('department_id') == $id ? 'selected' : '' }}>
                {{ $name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="employment_type" class="form-select">
            <option value="">All Types</option>
            @foreach($employmentTypes as $key => $label)
              <option value="{{ $key }}" {{ request('employment_type') == $key ? 'selected' : '' }}>
                {{ $label }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <input type="text" name="search" class="form-control"
                 placeholder="Search name, code or email…" value="{{ request('search') }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-fill">Search</button>
          <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
        </div>
      </form>

      {{-- Table --}}
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Code</th>
              <th>Status</th>
              <th>Name</th>
              <th>Email</th>
              <th>Dept</th>
              <th>Type</th>
              <th>Schedule</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($employees as $e)
              @php
                // Build one clean payload per employee for both View and Edit modals
                $payload = [
                  // Summary / view
                  'employee_code'     => $e->employee_code,
                  'full_name'         => $e->name,
                  'email'             => optional($e->user)->email,
                  'role'              => optional($e->user)->getRoleNames()->first(),
                  'employment_status' => ucfirst($e->status),
                  'department'        => optional($e->department)->name,
                  'designation'       => optional($e->designation)->name,
                  'schedule_in'       => optional($e->schedule)->time_in,
                  'schedule_out'      => optional($e->schedule)->time_out,

                  // Account (edit)
                  'status'            => $e->status,

                  // Personal (edit)
                  'first_name'             => $e->first_name,
                  'middle_name'            => $e->middle_name,
                  'last_name'              => $e->last_name,
                  'gender'                 => $e->gender,
                  'dob'                    => optional($e->dob)->format('Y-m-d'),
                  'current_street_address' => $e->current_street_address,
                  'current_province'       => $e->current_province,
                  'current_city'           => $e->current_city,
                  'current_postal_code'    => $e->current_postal_code,
                  'permanent_address'      => $e->permanent_address,

                  // Work (edit)
                  'department_id'         => $e->department_id,
                  'designation_id'        => $e->designation_id,
                  'schedule_id'           => $e->schedule_id,
                  'employment_type'       => $e->employment_type,
                  'employment_start_date' => optional($e->employment_start_date)->format('Y-m-d'),
                  'employment_end_date'   => optional($e->employment_end_date)->format('Y-m-d'),
                  'fingerprint_id'        => $e->fingerprint_id,

                  // Benefits (edit)
                  'gsis_id_no'            => $e->gsis_id_no,
                  'pagibig_id_no'         => $e->pagibig_id_no,
                  'philhealth_tin_id_no'  => $e->philhealth_tin_id_no,
                  'sss_no'                => $e->sss_no,
                  'tin_no'                => $e->tin_no,
                  'agency_employee_no'    => $e->agency_employee_no,

                  // Education
                  'elementary_school'           => $e->elementary_school,
                  'elementary_year_graduated'   => $e->elementary_year_graduated,
                  'high_school'                 => $e->high_school,
                  'high_school_year_graduated'  => $e->high_school_year_graduated,
                  'college'                     => $e->college,
                  'college_year_graduated'      => $e->college_year_graduated,
                  'degree_received'             => $e->degree_received,
                  'special_skills'              => $e->special_skills,

                  // Employment history
                  'emp1_company' => $e->emp1_company,
                  'emp1_position'=> $e->emp1_position,
                  'emp1_from'    => optional($e->emp1_from)->format('Y-m-d'),
                  'emp1_to'      => optional($e->emp1_to)->format('Y-m-d'),
                  'emp2_company' => $e->emp2_company,
                  'emp2_position'=> $e->emp2_position,
                  'emp2_from'    => optional($e->emp2_from)->format('Y-m-d'),
                  'emp2_to'      => optional($e->emp2_to)->format('Y-m-d'),

                  // Character references
                  'char1_name'     => $e->char1_name,
                  'char1_position' => $e->char1_position,
                  'char1_company'  => $e->char1_company,
                  'char1_contact'  => $e->char1_contact,
                  'char2_name'     => $e->char2_name,
                  'char2_position' => $e->char2_position,
                  'char2_company'  => $e->char2_company,
                  'char2_contact'  => $e->char2_contact,

                  // Certificates & IDs
                  'res_cert_no'        => $e->res_cert_no,
                  'res_cert_issued_at' => $e->res_cert_issued_at,
                  'res_cert_issued_on' => optional($e->res_cert_issued_on)->format('Y-m-d'),
                  'nbi_no'             => $e->nbi_no,
                  'passport_no'        => $e->passport_no,
                ];
              @endphp

              <tr>
                <td>{{ $e->id }}</td>
                <td>{{ $e->employee_code }}</td>
                <td><span class="badge bg-primary">{{ ucfirst($e->status) }}</span></td>
                <td>{{ $e->name }}</td>
                <td>{{ optional($e->user)->email }}</td>
                <td>{{ optional($e->department)->name }}</td>
                <td>{{ ucfirst($e->employment_type) }}</td>
                <td>{{ optional($e->schedule)->time_in }}–{{ optional($e->schedule)->time_out }}</td>
                <td class="text-center">

                  {{-- View -> shared modal --}}
                  <button
                    type="button"
                    class="btn btn-outline-primary btn-sm me-1"
                    data-bs-toggle="modal"
                    data-bs-target="#viewEmployeeModal"
                    data-employee='@json($payload)'
                  >
                    <i class="bi bi-eye"></i>
                  </button>

                  {{-- Edit -> shared modal (not a page) --}}
                  <button
                    type="button"
                    class="btn btn-outline-warning btn-sm me-1"
                    data-bs-toggle="modal"
                    data-bs-target="#editEmployeeModal"
                    data-action="{{ route('employees.update', $e) }}"
                    data-employee='@json($payload)'
                  >
                    <i class="bi bi-pencil"></i>
                  </button>

                  {{-- Delete --}}
                  <form action="{{ route('employees.destroy', $e) }}"
                        method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-4">
        <small class="text-muted">
          Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }}
        </small>
        {{ $employees->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

{{-- Add Employee Modal --}}
@include('employees.create')

{{-- View Employee Modal (shared) --}}
@include('employees.show')

{{-- Edit Employee Modal (shared) --}}
@include('employees.edit-modal')

@endsection
