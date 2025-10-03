{{-- resources/views/timecard/index.blade.php --}}
@extends('layouts.app')

@section('page_title', 'My Time Card')

@push('styles')
<style>
  /* Polished, theme-aligned badges */
  .badge-theme {
    font-weight: 600;
    letter-spacing: .2px;
    padding: .35rem .65rem;
    border-radius: 999px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
  }
  /* Optional soft variant if you want to use it later */
  .badge-soft-primary {
    color: var(--bs-primary);
    background: rgba(var(--bs-primary-rgb, 67,56,202), .12);
    border: 1px solid rgba(var(--bs-primary-rgb, 67,56,202), .24);
  }
  .table-timecard td,
  .table-timecard th {
    vertical-align: middle;
  }
  @media (max-width: 768px) {
    .timecard-actions {
      gap: .5rem;
      flex-direction: column;
      align-items: stretch !important;
    }
  }
</style>
@endpush

@section('content')
@php
  /**
   * Map status strings to theme-aligned Bootstrap classes.
   * - Present / On Time -> brand primary
   * - Late / In-Progress -> warning with dark text
   * - Absent / Violation -> danger
   * - Leave -> info with dark text
   * - Suspended -> secondary
   */
  function tc_badge(string $status): string {
      $s = strtolower($status);
      return match (true) {
          str_contains($s, 'present'),
          str_contains($s, 'on time')      => 'bg-primary text-white',
          str_contains($s, 'in-progress'),
          str_contains($s, 'late')         => 'bg-warning text-dark',
          str_contains($s, 'absent'),
          str_contains($s, 'violation')    => 'bg-danger text-white',
          str_contains($s, 'leave')        => 'bg-info text-dark',
          str_contains($s, 'suspend')      => 'bg-secondary text-white',
          default                          => 'bg-secondary text-white',
      };
  }
@endphp

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">
      <i class="bi bi-journal-check me-2"></i> My Time Card
    </h4>

    <form class="d-flex align-items-center gap-2 timecard-actions"
          action="{{ route('timecard.index') }}"
          method="get">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
        <input type="date" name="start" class="form-control" value="{{ $start }}">
      </div>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
        <input type="date" name="end" class="form-control" value="{{ $end }}">
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-search me-1"></i> Apply
      </button>

      {{-- Optional: CSV export for the same range (route exists in your setup) --}}
      <a class="btn btn-outline-secondary"
         href="{{ route('timecard.exportCsv', ['start' => $start, 'end' => $end]) }}">
        <i class="bi bi-filetype-csv me-1"></i> Export CSV
      </a>
    </form>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-timecard mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 28%">Date</th>
              <th style="width: 22%">Time In</th>
              <th style="width: 22%">Time Out</th>
              <th style="width: 12%">Hours</th>
              <th style="width: 16%">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $r)
              <tr>
                <td>{{ \Carbon\Carbon::parse($r['date'])->format('D, M d, Y') }}</td>
                <td>{{ $r['time_in'] }}</td>
                <td>{{ $r['time_out'] }}</td>
                <td>{{ $r['hours'] }}</td>
                <td>
                  <span class="badge badge-theme {{ tc_badge($r['status']) }}">
                    {{ $r['status'] }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center py-5 text-muted">
                  No records found for the selected dates.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
