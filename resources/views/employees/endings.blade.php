@extends('layouts.app')

@section('page_title','Ending Soon')

@section('content')
<div class="container-fluid">
  {{-- Header --}}
  <div class="row mb-4">
    <div class="col"><h3><i class="bi-clock me-1"></i> Ending Soon</h3></div>
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
            <td id="start-{{ $emp->id }}">{{ optional($emp->employment_start_date)->toDateString() }}</td>
            <td id="end-{{ $emp->id }}">{{ optional($emp->employment_end_date)->toDateString() }}</td>
            <td>{{ $emp->schedule?->name }}</td>
            <td class="text-nowrap">
              @php $actions = $actionMap[$emp->employment_type] ?? []; @endphp

              @foreach($actions as $act)
                @if($act['route'] !== 'employees.terminate')
                  <button
                    class="btn btn-sm btn-success adjust-btn"
                    data-route="{{ route($act['route'], $emp) }}"
                    data-current-start="{{ optional($emp->employment_start_date)->toDateString() }}"
                    data-current-end="{{ optional($emp->employment_end_date)->toDateString() }}"
                  >{{ $act['label'] }}</button>
                @else
                  <form method="POST" action="{{ route($act['route'], $emp) }}" class="d-inline"
                        onsubmit="return confirm('Are you sure to {{ strtolower($act['label']) }} for {{ $emp->employee_code }}?');">
                    @csrf @method('delete')
                    <button class="btn btn-sm btn-danger">{{ $act['label'] }}</button>
                  </form>
                @endif
              @endforeach
            </td>
          </tr>
        @empty
          <tr><td colspan="10" class="text-center">No employees ending soon.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-3">{{ $employees->links() }}</div>
</div>

{{-- Modal --}}
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
document.addEventListener('DOMContentLoaded', ()=>{
  const modalEl        = document.getElementById('adjustModal');
  const adjustForm     = document.getElementById('adjustForm');
  const modalCurStart  = document.getElementById('modalCurrentStart');
  const modalNewStart  = document.getElementById('modalNewStart');
  const modalCurEnd    = document.getElementById('modalCurrentEnd');
  const modalNewEnd    = document.getElementById('modalNewEnd');

  document.querySelectorAll('.adjust-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      adjustForm.action            = btn.dataset.route;
      modalCurStart.value          = btn.dataset.currentStart;
      modalNewStart.value          = btn.dataset.currentStart;
      modalCurEnd.value            = btn.dataset.currentEnd;
      modalNewEnd.value            = btn.dataset.currentEnd;
      new bootstrap.Modal(modalEl).show();
    });
  });
});
</script>
@endpush
