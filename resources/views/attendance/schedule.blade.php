{{-- resources/views/attendance/schedule.blade.php --}}
@extends('layouts.app')

@section('page_title','Schedules')

@section('content')
<div class="container-fluid py-4">
  {{-- ── Add New Shift ───────────────────────────────── --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex align-items-center">
      <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i> Add New Shift</h4>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('schedule.store') }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-3">
          <label class="form-label">Name <small>(no spaces)</small></label>
          <input type="text" name="name" class="form-control" placeholder="e.g., Shift-One" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Time In</label>
          <input type="time" name="time_in" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Time Out</label>
          <input type="time" name="time_out" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Rest Day</label>
          <select name="rest_day" class="form-select">
            <option value="">— none —</option>
            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
              <option value="{{ $day }}">{{ $day }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-primary">Save Shift</button>
        </div>
      </form>
    </div>
  </div>

  {{-- ── Existing Shifts ───────────────────────────────── --}}
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center">
      <h4 class="mb-0"><i class="bi bi-list-check me-2"></i> Existing Shifts</h4>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:1%">#</th>
              <th>Shift</th>
              <th>In</th>
              <th>Out</th>
              <th>Rest Day</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($schedules as $i => $sched)
              <tr>
                <td>{{ $schedules->firstItem() + $i }}</td>
                <td>{{ $sched->name }}</td>
                <td>{{ \Carbon\Carbon::parse($sched->time_in)->format('H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($sched->time_out)->format('H:i') }}</td>
                <td>{{ $sched->rest_day ?? '—' }}</td>
                <td class="text-end">
                  <button type="button"
                          class="btn btn-sm btn-warning edit-button"
                          data-id="{{ $sched->id }}"
                          data-name="{{ $sched->name }}"
                          data-time_in="{{ $sched->time_in }}"
                          data-time_out="{{ $sched->time_out }}"
                          data-rest_day="{{ $sched->rest_day }}">
                    Edit
                  </button>
                  <form action="{{ route('schedule.destroy', $sched) }}"
                        method="POST"
                        class="d-inline"
                        onsubmit="return confirm('Delete this shift?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  No shifts defined yet.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      {{ $schedules->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('editScheduleModal');
    const modal   = new bootstrap.Modal(modalEl);
    const form    = document.getElementById('editScheduleForm');
    const baseUrl = @json(route('schedule.index', [], false));

    document.querySelectorAll('.edit-button').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('edit-name').value     = this.dataset.name;
        document.getElementById('edit-time_in').value  = this.dataset.time_in;
        document.getElementById('edit-time_out').value = this.dataset.time_out;
        document.getElementById('edit-rest_day').value = this.dataset.rest_day || '';

        form.action = `${baseUrl}/${this.dataset.id}`;
        modal.show();
      });
    });
  });
</script>
@endpush
