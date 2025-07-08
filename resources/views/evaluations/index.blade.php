{{-- resources/views/evaluations/index.blade.php --}}
@extends('layouts.app')

@section('page_title','Performance Evaluation')

@section('content')
<div class="container-fluid py-4">

  {{-- Tabs --}}
  <div class="d-flex gap-2 mb-4">
    <a href="{{ route('performance_forms.index') }}"
       class="btn btn-sm {{ request()->routeIs('performance_forms.index') ? 'btn-primary':'btn-outline-primary' }}">
      Add Evaluation
    </a>
    <a href="{{ route('evaluations.completed') }}"
       class="btn btn-sm {{ request()->routeIs('evaluations.completed') ? 'btn-primary':'btn-outline-primary' }}">
      Completed Evaluations
    </a>
  </div>

  {{-- Fill Evaluations --}}
  @if(request()->routeIs('evaluations.index'))
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white d-flex align-items-center">
        <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Fill Evaluations</h4>
      </div>
      <div class="card-body p-0">
        @if($pending->isEmpty())
          <div class="alert alert-info m-4">No pending evaluations.</div>
        @else
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Employee</th>
                  <th>Form</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                @php $today = \Carbon\Carbon::today(); @endphp

                @foreach($pending as $a)
                  @php
                    // only compare if the dates actually exist
                    $notStarted   = $a->starts_at && $today->lt($a->starts_at);
                    $alreadyEnded = $a->ends_at   && $today->gt($a->ends_at);
                  @endphp

                  <tr>
                    <td>{{ $a->employee->user->name }}</td>
                    <td>{{ $a->form->title }}</td>
                    <td class="text-end">
                      <button
                        class="btn btn-sm btn-primary"
                        onclick="location.href='{{ url('evaluations') }}/{{ $a->form_id }}/{{ $a->employee_id }}'"
                        @disabled($notStarted || $alreadyEnded)
                      >
                        @if($notStarted)
                          Opens {{ $a->starts_at->format('M d') }}
                        @elseif($alreadyEnded)
                          Closed
                        @else
                          Evaluate
                        @endif
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      {{-- Pagination --}}
      <div class="card-footer d-flex justify-content-between">
        @if($pending->previousPageUrl())
          <a href="{{ $pending->previousPageUrl() }}" class="btn btn-outline-primary">‹ Previous</a>
        @else
          <button class="btn btn-outline-secondary" disabled>‹ Previous</button>
        @endif

        @if($pending->nextPageUrl())
          <a href="{{ $pending->nextPageUrl() }}" class="btn btn-outline-primary">Next ›</a>
        @else
          <button class="btn btn-outline-secondary" disabled>Next ›</button>
        @endif
      </div>
    </div>
  @endif

  {{-- Completed Evaluations --}}
  @if(request()->routeIs('evaluations.completed'))
    <div class="card shadow-sm">
      <div class="card-header bg-white d-flex align-items-center">
        <h4 class="mb-0"><i class="bi bi-check2-circle me-2"></i> Completed Evaluations</h4>
      </div>
      <div class="card-body p-0">
        @if($completed->isEmpty())
          <div class="alert alert-info m-4">No completed evaluations yet.</div>
        @else
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Form</th>
                  <th>Employee</th>
                  <th>Date</th>
                  <th>Total Score</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($completed as $ev)
                  <tr>
                    <td>{{ $ev->form->title }}</td>
                    <td>{{ $ev->employee->user->name }}</td>
                    <td>{{ $ev->evaluated_on->format('Y-m-d') }}</td>
                    <td>{{ $ev->total_score }}</td>
                    <td class="text-end">
                      <a href="{{ route('evaluations.show',[$ev->form_id,$ev->employee_id]) }}"
                         class="btn btn-sm btn-info">View</a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
      <div class="card-footer d-flex justify-content-between">
        @if($completed->previousPageUrl())
          <a href="{{ $completed->previousPageUrl() }}" class="btn btn-outline-primary">‹ Previous</a>
        @else
          <button class="btn btn-outline-secondary" disabled>‹ Previous</button>
        @endif

        @if($completed->nextPageUrl())
          <a href="{{ $completed->nextPageUrl() }}" class="btn btn-outline-primary">Next ›</a>
        @else
          <button class="btn btn-outline-secondary" disabled>Next ›</button>
        @endif
      </div>
    </div>
  @endif

</div>
@endsection
