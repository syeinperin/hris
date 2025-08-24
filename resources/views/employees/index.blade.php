{{-- resources/views/employees/index.blade.php --}}
@extends('layouts.app')

@push('styles')
  <style>
    /* Keep dropdown menus above sticky areas / footer inside the scroll container */
    .table-scroll .dropdown-menu { z-index: 1056; }
  </style>
@endpush

@push('scripts')
  <script src="{{ asset('js/ph-location.js') }}"></script>
@endpush

@section('page_title', 'Employees')

@section('content')
<div class="container-fluid">

  <div class="card shadow-sm mb-4">
    {{-- Header / primary actions --}}
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="bi bi-people-fill me-2"></i> Employees
      </h4>
      <div class="d-flex align-items-center gap-2">
        <a href="{{ route('employees.endings') }}" class="btn btn-outline-warning btn-sm">
          <i class="bi bi-exclamation-triangle me-1"></i> Ending Soon
          <span class="badge bg-warning text-dark">{{ $endingCount }}</span>
        </a>
        <a href="{{ route('employees.inactive') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-person-x me-1"></i> Inactive
          <span class="badge bg-secondary">{{ $inactiveCount }}</span>
        </a>
        <a href="{{ route('departments.index') }}" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-building me-1"></i> Departments
        </a>

        {{-- Go to create page (no modal) --}}
        <a href="{{ route('employees.create') }}" class="btn btn-success btn-sm">
          <i class="bi bi-plus-lg me-1"></i> Add
        </a>
      </div>
    </div>

    {{-- Filters --}}
    <div class="px-3 pt-3 pb-1 filter-bar">
      <x-search-bar
        :action="route('employees.index')"
        placeholder="Search name, code or email…"
        :filters="[
          'department_id'   => $departments,
          'employment_type' => $employmentTypes,
        ]"
      />
    </div>

    <div class="card-body pt-2">
      {{-- Scrollable table with sticky header --}}
      <div class="table-scroll">
        <table class="table table-hover align-middle mb-0 table-sticky">
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
              <th class="text-center" style="width:56px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($employees as $e)
              @php
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

                // For bottom rows, open the actions menu upward
                $dropUp = ($loop->count - $loop->iteration) < 3;
              @endphp

              <tr>
                <td>{{ $e->id }}</td>
                <td>{{ $e->employee_code }}</td>
                <td>
                  <span class="badge bg-primary rounded-pill px-2 py-1">
                    {{ ucfirst($e->status) }}
                  </span>
                </td>
                <td>{{ $e->name }}</td>
                <td>{{ optional($e->user)->email }}</td>
                <td>{{ optional($e->department)->name }}</td>
                <td>{{ ucfirst($e->employment_type) }}</td>
                <td>{{ optional($e->schedule)->time_in }}–{{ optional($e->schedule)->time_out }}</td>
                <td class="text-center">
                  {{-- Prevent clipping & flip up when near the footer --}}
                  <div class="dropdown position-static {{ $dropUp ? 'dropup' : '' }}">
                    <button
                      class="btn btn-outline-primary btn-sm"
                      data-bs-toggle="dropdown"
                      data-bs-display="dynamic"
                      data-bs-boundary="viewport"
                      data-bs-offset="0,8"
                      aria-expanded="false">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                      <li>
                        <button
                          class="dropdown-item"
                          data-bs-toggle="modal"
                          data-bs-target="#viewEmployeeModal"
                          data-employee='@json($payload)'>
                          <i class="bi bi-eye me-2"></i> View
                        </button>
                      </li>
                      <li>
                        <button
                          class="dropdown-item"
                          data-bs-toggle="modal"
                          data-bs-target="#editEmployeeModal"
                          data-action="{{ route('employees.update', $e) }}"
                          data-employee='@json($payload)'>
                          <i class="bi bi-pencil me-2"></i> Edit
                        </button>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <form action="{{ route('employees.destroy', $e) }}" method="POST"
                              onsubmit="return confirm('Are you sure?')">
                          @csrf @method('DELETE')
                          <button class="dropdown-item text-danger">
                            <i class="bi bi-trash me-2"></i> Delete
                          </button>
                        </form>
                      </li>
                    </ul>
                  </div>
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
        {{ $employees->withQueryString()->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

{{-- Keep the view/edit modals only --}}
@include('employees.show')
@include('employees.edit-modal')

@endsection
