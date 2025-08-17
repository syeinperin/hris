@extends('layouts.app')
@section('page_title','New Disciplinary Action')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bi bi-plus-square me-2"></i> New Action</h4>
      <a href="{{ route('discipline.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
      </a>
    </div>

    <div class="card-body">
      <form action="{{ route('discipline.store') }}" method="POST" class="row g-3">
        @csrf

        <div class="col-md-6">
          <label class="form-label">Employee</label>
          <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
            <option value="">-- choose employee --</option>
            @foreach($employees as $id => $name)
              <option value="{{ $id }}" {{ old('employee_id')==$id?'selected':'' }}>{{ $name }}</option>
            @endforeach
          </select>
          @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-3">
          <label class="form-label">Action Type</label>
          <select id="action_type" name="action_type" class="form-select" required>
            <option value="violation"  {{ old('action_type')==='violation'?'selected':'' }}>Violation</option>
            <option value="suspension" {{ old('action_type')==='suspension'?'selected':'' }}>Suspension</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Severity</label>
          <select name="severity" class="form-select" required>
            @foreach(['minor','major','critical'] as $s)
              <option value="{{ $s }}" {{ old('severity')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Category (optional)</label>
          <input name="category" value="{{ old('category') }}" class="form-control" placeholder="Attendance, Conduct, Safety...">
        </div>

        <div class="col-md-2">
          <label class="form-label">Points (optional)</label>
          <input type="number" name="points" min="0" max="100" value="{{ old('points') }}" class="form-control">
        </div>

        <div id="suspension_dates" class="col-12 row g-3 {{ old('action_type','violation')==='suspension' ? '' : 'd-none' }}">
          <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" value="{{ old('end_date') }}" class="form-control">
          </div>
        </div>

        <div class="col-12">
          <label class="form-label">Reason / Description</label>
          <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
          @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-12">
          <label class="form-label">Notes (optional)</label>
          <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <button class="btn btn-success"><i class="bi bi-check2-circle me-1"></i> Save</button>
          <a href="{{ route('discipline.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const typeSel = document.getElementById('action_type');
  const block   = document.getElementById('suspension_dates');
  if (typeSel) {
    typeSel.addEventListener('change', () => {
      if (typeSel.value === 'suspension') block.classList.remove('d-none');
      else block.classList.add('d-none');
    });
  }
});
</script>
@endpush
