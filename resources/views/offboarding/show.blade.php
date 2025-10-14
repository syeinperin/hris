{{-- resources/views/offboarding/show.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Offboarding #'.$offboarding->id)

@push('styles')
<style>
  .ofb-status-pill{
    text-transform: capitalize;
    font-weight: 600;
    letter-spacing:.25px;
  }
  .ofb-card{
    border:1px solid #edf0f4;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(18,38,63,.06);
  }
  .ofb-meta dt{
    width:140px;
    color:#6b7a90;
  }
  .ofb-meta dd{
    margin-left:160px;
    font-weight:600;
    color:#24324a;
  }
  .ofb-actions .btn{
    min-width:140px;
  }
  .ofb-actions .btn i{
    margin-right:6px;
  }
  .ofb-toolbar{
    position:sticky;
    bottom:0;
    background:#fff;
    border-top:1px solid #eef2f7;
    padding:10px 16px;
    z-index:100;
  }
  @media (max-width: 576px){
    .ofb-meta dt{ width:120px; }
    .ofb-meta dd{ margin-left:130px; }
  }
</style>
@endpush

@push('scripts')
<script>
  // Optional: auto-submit on ENTER in date field
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('ofb-schedule-form');
    const input = document.getElementById('scheduled_at');
    if(form && input){
      input.addEventListener('keydown', (e) => {
        if(e.key === 'Enter'){ e.preventDefault(); form.submit(); }
      });
    }
  });
</script>
@endpush

@section('content')
<div class="container-fluid">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-1">Offboarding #{{ $offboarding->id }}</h3>
      <span class="badge bg-secondary ofb-status-pill">
        {{ str_replace('_',' ', $offboarding->status) }}
      </span>
    </div>
    <a href="{{ route('offboarding.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> Back
    </a>
  </div>

  <div class="ofb-card p-4 mb-4">
    <div class="row g-4">

      {{-- LEFT: main facts --}}
      <div class="col-12 col-lg-7">
        <dl class="ofb-meta mb-0">
          <dt>Employee</dt>
          <dd>
            {{ $offboarding->employee->employee_code }}
            — {{ $offboarding->employee->name }}
          </dd>

          <dt>Type</dt>
          <dd>{{ $offboarding->type ? ucfirst($offboarding->type) : '—' }}</dd>

          <dt>Reason</dt>
          <dd>{{ $offboarding->reason ?: '—' }}</dd>

          <dt>Schedule</dt>
          <dd>
            {{ $offboarding->scheduled_at ? $offboarding->scheduled_at->format('d/m/Y h:i a') : '—' }}
          </dd>
        </dl>
      </div>

      {{-- RIGHT: actions --}}
      <div class="col-12 col-lg-5">
        <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
          <div class="mb-3">
            <div class="small text-muted mb-2">Set Offboarding Appointment</div>
            <form id="ofb-schedule-form"
                  action="{{ route('offboarding.schedule', $offboarding) }}"
                  method="POST"
                  class="d-flex gap-2">
              @csrf
              @method('PATCH')
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                <input
                  id="scheduled_at"
                  type="datetime-local"
                  name="scheduled_at"
                  class="form-control"
                  value="{{ $offboarding->scheduled_at ? $offboarding->scheduled_at->format('Y-m-d\TH:i') : '' }}"
                  required
                />
              </div>
              <button class="btn btn-primary">
                <i class="bi bi-calendar2-check"></i> Schedule
              </button>
            </form>
          </div>

          <div class="ofb-actions d-flex flex-wrap gap-2">
            {{-- Pending Clearance --}}
            @if($offboarding->status !== 'pending_clearance')
              <form action="{{ route('offboarding.pendingClearance', $offboarding) }}" method="POST">
                @csrf @method('PATCH')
                <button class="btn btn-outline-warning">
                  <i class="bi bi-hourglass-split"></i> Pending Clearance
                </button>
              </form>
            @else
              <button class="btn btn-outline-warning" disabled>
                <i class="bi bi-hourglass-split"></i> Pending Clearance
              </button>
            @endif

            {{-- Complete --}}
            <form action="{{ route('offboarding.complete', $offboarding) }}" method="POST"
                  onsubmit="return confirm('Mark offboarding as complete?')">
              @csrf @method('PATCH')
              <button class="btn btn-success">
                <i class="bi bi-check2-circle"></i> Complete
              </button>
            </form>

            {{-- Cancel --}}
            <form action="{{ route('offboarding.cancel', $offboarding) }}" method="POST"
                  onsubmit="return confirm('Cancel this offboarding?')">
              @csrf @method('DELETE')
              <button class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Cancel
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- bottom sticky toolbar (quick actions) --}}
  <div class="ofb-toolbar d-flex align-items-center gap-2">
    <strong class="me-auto">Offboarding #{{ $offboarding->id }}</strong>
    <a class="btn btn-light btn-sm" href="{{ route('offboarding.index') }}">
      <i class="bi bi-list-ul"></i> All Offboarding
    </a>
    <a class="btn btn-light btn-sm" href="{{ route('employees.index') }}">
      <i class="bi bi-people"></i> Employee List
    </a>
  </div>

</div>
@endsection
