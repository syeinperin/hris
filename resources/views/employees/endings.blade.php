{{-- resources/views/employees/endings.blade.php --}}
@extends('layouts.app')

@section('page_title','Ending Soon')

@push('styles')
<style>
  /* ensure no clipping */
  .table-wrap { overflow-x: auto; }
  .table-ending { min-width: 1100px; } /* wider than the content so actions/columns don't squeeze */
  .table-actions { white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="container-fluid">

  {{-- Header --}}
  <div class="row mb-4 align-items-center">
    <div class="col">
      <h3 class="mb-0"><i class="bi bi-clock me-2"></i> Ending Soon</h3>
    </div>
    <div class="col text-end">
      <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">← All Employees</a>
    </div>
  </div>

  {{-- Filters --}}
  <form method="GET" action="{{ route('employees.endings') }}" class="row g-2 mb-4">
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
      <a href="{{ route('employees.endings') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div class="table-wrap">
    <table class="table table-striped align-middle table-ending">
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
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($employees as $emp)
          <tr>
            <td>{{ $emp->id }}</td>
            <td>{{ $emp->employee_code }}</td>
            <td>{{ $emp->name }}</td>
            <td>{{ optional($emp->user)->email }}</td>
            <td>{{ optional($emp->department)->name }}</td>
            <td>{{ ucfirst($emp->employment_type) }}</td>
            <td id="start-{{ $emp->id }}">{{ optional($emp->employment_start_date)->toDateString() }}</td>
            <td id="end-{{ $emp->id }}">{{ optional($emp->employment_end_date)->toDateString() }}</td>
            <td>
              @if($emp->schedule)
                {{ $emp->schedule->time_in }}–{{ $emp->schedule->time_out }}
              @else
                —
              @endif
            </td>
            <td class="text-center table-actions">
              @php $actions = $actionMap[$emp->employment_type] ?? []; @endphp

              {{-- compact dropdown to avoid overflow/cut --}}
              <div class="dropdown d-inline-block">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  Manage
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                  @forelse($actions as $act)
                    @if($act['route'] !== 'employees.terminate')
                      <a href="#" class="dropdown-item adjust-link"
                         data-route="{{ route($act['route'], $emp) }}"
                         data-current-start="{{ optional($emp->employment_start_date)->toDateString() }}"
                         data-current-end="{{ optional($emp->employment_end_date)->toDateString() }}">
                        {{ $act['label'] }}
                      </a>
                    @else
                      <form method="POST" action="{{ route($act['route'], $emp) }}"
                            onsubmit="return confirm('Are you sure to {{ strtolower($act['label']) }} for {{ $emp->employee_code }}?');">
                        @csrf @method('delete')
                        <button type="submit" class="dropdown-item text-danger">{{ $act['label'] }}</button>
                      </form>
                    @endif
                  @empty
                    <span class="dropdown-item text-muted">No actions</span>
                  @endforelse
                </div>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="text-center">No employees ending soon.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-3">
    {{ $employees->links('pagination::bootstrap-5') }}
  </div>
</div>

{{-- Adjust Dates Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="adjustForm" method="POST">
      @csrf @method('PATCH')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Adjust Employment Dates</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Current Start Date</label>
            <input type="text" id="modalCurrentStart" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">New Start Date</label>
            <input type="date" name="new_start_date" id="modalNewStart" class="form-control" required>
          </div>
          <hr>
          <div class="mb-3">
            <label class="form-label">Current End Date</label>
            <input type="text" id="modalCurrentEnd" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">New End Date</label>
            <input type="date" name="new_end_date" id="modalNewEnd" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Dates</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', ()=> {
  const modalEl        = document.getElementById('adjustModal');
  const adjustForm     = document.getElementById('adjustForm');
  const modalCurStart  = document.getElementById('modalCurrentStart');
  const modalNewStart  = document.getElementById('modalNewStart');
  const modalCurEnd    = document.getElementById('modalCurrentEnd');
  const modalNewEnd    = document.getElementById('modalNewEnd');
  const bsModal        = new bootstrap.Modal(modalEl);

  // open modal from dropdown items
  document.querySelectorAll('.adjust-link').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      adjustForm.action   = a.dataset.route;
      modalCurStart.value = a.dataset.currentStart || '';
      modalNewStart.value = a.dataset.currentStart || '';
      modalCurEnd.value   = a.dataset.currentEnd   || '';
      modalNewEnd.value   = a.dataset.currentEnd   || '';
      bsModal.show();
    });
  });
});
</script>
@endpush
