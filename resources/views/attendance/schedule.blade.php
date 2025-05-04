@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-4">Schedules</h3>

  {{-- Success & Errors --}}
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

  {{-- Create Form --}}
  <form action="{{ route('schedule.store') }}" method="POST" class="mb-4">
    @csrf
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Name <small>(no spaces)</small></label>
        <input type="text" name="name" class="form-control" placeholder="e.g., Shift-One" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Time In</label>
        <input type="time" name="time_in" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Time Out</label>
        <input type="time" name="time_out" class="form-control" required>
      </div>
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </div>
  </form>

  {{-- Schedules Table --}}
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark">
      <tr>
        <th style="width:1%">#</th>
        <th>Shift</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @foreach($schedules as $i => $sched)
      <tr>
        <td>{{ $schedules->firstItem() + $i }}</td>
        <td>{{ $sched->name }}</td>
        <td>{{ $sched->time_in }}</td>
        <td>{{ $sched->time_out }}</td>
        <td>
          <button type="button"
                  class="btn btn-warning btn-sm edit-button"
                  data-id="{{ $sched->id }}"
                  data-name="{{ $sched->name }}"
                  data-time_in="{{ $sched->time_in }}"
                  data-time_out="{{ $sched->time_out }}">
            Edit
          </button>

          <form action="{{ route('schedule.destroy', $sched) }}"
                method="POST"
                class="d-inline"
                onsubmit="return confirm('Delete this shift?');">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger btn-sm">Delete</button>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Pagination --}}
  <div class="d-flex justify-content-center">
    {{ $schedules->withQueryString()->links() }}
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editScheduleForm" method="POST" action="">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editScheduleModalLabel">Edit Schedule</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input id="edit-name" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Time In</label>
            <input id="edit-time_in" name="time_in" type="time" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Time Out</label>
            <input id="edit-time_out" name="time_out" type="time" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('editScheduleModal');
    const modal   = new bootstrap.Modal(modalEl);
    const form    = document.getElementById('editScheduleForm');
    const baseUrl = @json(url('schedule'));

    document.querySelectorAll('.edit-button').forEach(btn => {
      btn.addEventListener('click', function() {
        document.getElementById('edit-name').value     = this.dataset.name;
        document.getElementById('edit-time_in').value  = this.dataset.time_in;
        document.getElementById('edit-time_out').value = this.dataset.time_out;

        form.action = `${baseUrl}/${this.dataset.id}`;
        modal.show();
      });
    });
  });
</script>
@endsection
