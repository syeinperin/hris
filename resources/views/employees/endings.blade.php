{{-- resources/views/employees/endings.blade.php --}}
@extends('layouts.app')

@section('page_title','Ending Soon')

@push('styles')
<style>
  .table-wrap { overflow-x: auto; }
  .table-ending { min-width: 1100px; }
  .table-actions { white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="container-fluid">

  {{-- Header --}}
  <div class="row mb-4 align-items-center">
    <div class="col">
      <h3 class="mb-0"><i class="bi bi-clock me-2"></i> Ending Soon</h3>
      <div class="text-muted small">Employees with contracts ending soon, or probationary staff you may need to act on.</div>
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
      <button class="btn btn-primary me-2" type="submit"><i class="bi bi-funnel me-1"></i> Filter</button>
      <a href="{{ route('employees.endings') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  {{-- Table --}}
  <div class="table-wrap">
    <table class="table table-striped align-middle table-ending">
      <thead class="table-light">
        <tr>
          <th>#</th>
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
            <td>{{ optional($emp->employment_start_date)->toDateString() }}</td>
            <td>{{ optional($emp->employment_end_date)->toDateString() }}</td>
            <td>
              @if($emp->schedule)
                {{ $emp->schedule->time_in }}–{{ $emp->schedule->time_out }}
              @else
                —
              @endif
            </td>

            {{-- Actions --}}
            <td class="text-center table-actions">
              @if($emp->employment_type === 'probationary')
                <div class="dropdown d-inline-block">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Manage
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">

                    {{-- Promote to Regular (PATCH) --}}
                    <form class="px-3 py-1" method="POST"
                          action="{{ route('employees.regularize', $emp) }}"
                          onsubmit="return confirm('Promote {{ $emp->employee_code }} to Regular?');">
                      @csrf @method('PATCH')
                      <button type="submit" class="dropdown-item">Promote to Regular</button>
                    </form>

                    {{-- Extend Probation → opens modal --}}
                    <a href="#"
                       class="dropdown-item"
                       data-bs-toggle="modal"
                       data-bs-target="#extendModal"
                       data-route="{{ route('employees.extendProbation', $emp) }}"
                       data-current-end="{{ optional($emp->employment_end_date)->toDateString() }}">
                      Extend Probation
                    </a>

                    {{-- Reject Probation (DELETE) --}}
                    <form class="px-3 py-1" method="POST"
                          action="{{ route('employees.rejectProbation', $emp) }}"
                          onsubmit="return confirm('Reject probation for {{ $emp->employee_code }}?');">
                      @csrf @method('DELETE')
                      <button type="submit" class="dropdown-item text-danger">Reject Probation</button>
                    </form>
                  </div>
                </div>
              @else
                {{-- Fallback for non-probationary: Adjust dates (uses your existing Adjust modal) --}}
                <div class="dropdown d-inline-block">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Manage
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <a href="#" class="dropdown-item adjust-link"
                       data-route="{{ route('employees.extendTerm', $emp) }}"
                       data-current-start="{{ optional($emp->employment_start_date)->toDateString() }}"
                       data-current-end="{{ optional($emp->employment_end_date)->toDateString() }}">
                      Adjust Dates
                    </a>
                  </div>
                </div>
              @endif
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

{{-- ===================== Modals ===================== --}}

{{-- Adjust Dates Modal (keep for non-probationary) --}}
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

{{-- Extend Probation Modal --}}
<div class="modal fade" id="extendModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="extendForm" method="POST">
      @csrf @method('PATCH')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Extend Probation</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Current End Date</label>
            <input type="text" id="extendCurrentEnd" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Months to Extend</label>
            <select name="months" class="form-select" required>
              @for($m=1;$m<=6;$m++)
                <option value="{{ $m }}">{{ $m }} {{ \Illuminate\Support\Str::plural('month',$m) }}</option>
              @endfor
            </select>
            <div class="form-text">Allowed: 1 to 6 months.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Effective On (optional)</label>
            <input type="date" name="effective_on" class="form-control">
            <div class="form-text">Defaults to the later of today or the current end date.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason (optional)</label>
            <textarea name="reason" class="form-control" rows="2" placeholder="e.g., more time to evaluate attendance and quality"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Extend</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', ()=> {
  // Adjust Dates modal hook (for non-probationary)
  const adjustModal      = document.getElementById('adjustModal');
  const adjustForm       = document.getElementById('adjustForm');
  const modalCurStart    = document.getElementById('modalCurrentStart');
  const modalNewStart    = document.getElementById('modalNewStart');
  const modalCurEnd      = document.getElementById('modalCurrentEnd');
  const modalNewEnd      = document.getElementById('modalNewEnd');
  const bsAdjust         = adjustModal ? new bootstrap.Modal(adjustModal) : null;

  document.querySelectorAll('.adjust-link').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      if (!bsAdjust) return;
      adjustForm.action     = a.dataset.route;
      modalCurStart.value   = a.dataset.currentStart || '';
      modalNewStart.value   = a.dataset.currentStart || '';
      modalCurEnd.value     = a.dataset.currentEnd   || '';
      modalNewEnd.value     = a.dataset.currentEnd   || '';
      bsAdjust.show();
    });
  });

  // Extend Probation modal hook
  const extendModal  = document.getElementById('extendModal');
  const extendForm   = document.getElementById('extendForm');
  const extendEnd    = document.getElementById('extendCurrentEnd');

  extendModal?.addEventListener('show.bs.modal', (ev)=>{
    const a = ev.relatedTarget;
    if (!a) return;
    extendForm.action = a.getAttribute('data-route');
    extendEnd.value   = a.getAttribute('data-current-end') || '';
  });
});
</script>
@endpush
