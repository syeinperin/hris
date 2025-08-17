@extends('layouts.app')

@section('page_title','Performance Report')

@push('styles')
<style>
  .card-hdr { display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
  .stats-badge{ background:#f8f9fa; border:1px solid #e9ecef; border-radius:10px; padding:.35rem .6rem; font-weight:600; }
  .table thead th{ white-space:nowrap; }
  .section-title{ font-weight:700; font-size:1.1rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">

  <div class="mb-3">
    <h4 class="mb-0">Performance Report</h4>
  </div>

  {{-- === Evaluations === --}}
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="card-hdr mb-3">
        <div class="section-title d-flex align-items-center">
          <i class="bi bi-graph-up-arrow me-2"></i> Performance Report
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="stats-badge">Records: {{ number_format($evalCount) }}</span>
          <span class="stats-badge">Average: {{ number_format((float)$evalAvg, 2) }}%</span>
          <a class="btn btn-outline-secondary"
             href="{{ route('reports.performance.csv', request()->only('from','to')) }}">
            <i class="bi bi-download me-1"></i> Download CSV
          </a>
        </div>
      </div>

      {{-- Filter (shared by both sections) --}}
      <form method="GET" action="{{ route('reports.performance') }}" class="row g-2 mb-3">
        <div class="col-sm-4">
          <label class="form-label">From</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
            <input type="date" name="from" value="{{ $from }}" class="form-control" placeholder="yyyy-mm-dd">
          </div>
        </div>
        <div class="col-sm-4">
          <label class="form-label">To</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
            <input type="date" name="to" value="{{ $to }}" class="form-control" placeholder="yyyy-mm-dd">
          </div>
        </div>
        <div class="col-sm-4 d-flex align-items-end gap-2">
          <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
          <a href="{{ route('reports.performance') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Employee</th>
              <th>Period</th>
              <th>Overall %</th>
              <th>Evaluator</th>
              <th>Status</th>
              <th>Comments</th>
            </tr>
          </thead>
          <tbody>
          @forelse($evaluations as $i => $ev)
            <tr>
              <td>{{ $i+1 }}</td>
              <td class="text-nowrap">
                {{ $ev->employee->name ?? '—' }}
                <div class="small text-muted">{{ $ev->employee->employee_code ?? '' }}</div>
              </td>
              <td class="text-nowrap">
                {{ optional($ev->period_start)->toDateString() }} – {{ optional($ev->period_end)->toDateString() }}
              </td>
              <td class="fw-semibold">{{ number_format((float)$ev->overall_score, 2) }}%</td>
              <td>{{ $ev->evaluator->name ?? '—' }}</td>
              <td>
                <span class="badge {{ $ev->status==='submitted'?'bg-success':'bg-secondary' }}">
                  {{ ucfirst($ev->status) }}
                </span>
              </td>
              <td>{{ $ev->comments ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No evaluations found.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- === Violations / Suspensions === --}}
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="card-hdr mb-3">
        <div class="section-title d-flex align-items-center">
          <i class="bi bi-exclamation-octagon me-2"></i> Violations / Suspensions
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="stats-badge">Total: {{ number_format($actionsCount) }}</span>
          <span class="stats-badge">Violations: {{ number_format($violationsCnt) }}</span>
          <span class="stats-badge">Suspensions: {{ number_format($suspensionsCnt) }}</span>
          <a class="btn btn-outline-secondary"
             href="{{ route('reports.discipline.csv', request()->only('from','to')) }}">
            <i class="bi bi-download me-1"></i> Download CSV
          </a>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-striped align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Employee</th>
              <th>Type</th>
              <th>Category</th>
              <th>Severity</th>
              <th>Points</th>
              <th>Reason</th>
              <th>Status</th>
              <th>Period</th>
            </tr>
          </thead>
          <tbody>
          @forelse($actions as $i => $a)
            <tr>
              <td>{{ $i+1 }}</td>
              <td class="text-nowrap">{{ optional($a->start_date ?? $a->created_at)->toDateString() }}</td>
              <td class="text-nowrap">
                {{ $a->employee->name ?? '—' }}
                <div class="small text-muted">{{ $a->employee->employee_code ?? '' }}</div>
              </td>
              <td>
                <span class="badge {{ $a->action_type==='suspension'?'bg-danger':'bg-warning text-dark' }}">
                  {{ ucfirst($a->action_type) }}
                </span>
              </td>
              <td>{{ $a->category ?? '—' }}</td>
              <td>{{ ucfirst($a->severity) }}</td>
              <td>{{ $a->points !== null ? $a->points : '—' }}</td>
              <td class="text-wrap" style="max-width:360px">{{ $a->reason }}</td>
              <td>
                <span class="badge {{ $a->status==='active'?'bg-primary':'bg-secondary' }}">
                  {{ ucfirst($a->status) }}
                </span>
              </td>
              <td class="text-nowrap">
                @if($a->start_date || $a->end_date)
                  {{ optional($a->start_date)->toDateString() }} – {{ optional($a->end_date)->toDateString() }}
                @else
                  —
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="10" class="text-center text-muted py-4">No disciplinary actions found.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection
