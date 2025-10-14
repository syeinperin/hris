@extends('layouts.app')

@section('page_title')
  Attendance · {{ $employee->name }} ({{ \Carbon\Carbon::parse("$month-01")->format('F Y') }})
@endsection

@push('styles')
<style>
  .table thead th { white-space: nowrap; }
  .badge-legend .badge { font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid">

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="d-flex align-items-center gap-3">
        <h4 class="mb-0">
          <i class="bi bi-calendar3-week me-2"></i>
          {{ $employee->employee_code }} — {{ $employee->name }}
        </h4>
        <form method="GET" action="{{ route('attendance.show', $employee->id) }}" class="d-flex align-items-center gap-2">
          <input type="month" name="month" class="form-control form-control-sm" value="{{ $month }}">
          <button class="btn btn-primary btn-sm"><i class="bi bi-arrow-repeat me-1"></i> Go</button>
        </form>
      </div>

      <a href="{{ route('attendance.index', [
            'start_date' => $startOfMonth->toDateString(),
            'end_date'   => $endOfMonth->toDateString(),
            'search'     => $employee->employee_code
          ]) }}"
         class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left-short me-1"></i> Back to List
      </a>
    </div>

    <div class="card-body py-2">
      <div class="badge-legend d-flex flex-wrap gap-2 mb-3">
        <span class="badge bg-success">On Time</span>
        <span class="badge bg-warning">Late</span>
        <span class="badge bg-secondary">Absent</span>
        <span class="badge bg-info text-dark">On Leave</span>
        <span class="badge bg-dark">Suspended</span>
        <span class="badge bg-outline border border-1 text-muted">(+ Violation)</span>
      </div>

      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Time In</th>
              <th>Time Out</th>
              <th>OT (hr)</th>
              <th>Status</th>
              <th>Late (hr)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rows as $r)
              @php
                $status = (string) ($r['status'] ?? '');
                $badge = 'secondary';
                if (str_starts_with($status, 'On Time'))     $badge = 'success';
                elseif (str_starts_with($status, 'Late'))     $badge = 'warning';
                elseif (str_starts_with($status, 'Suspended'))$badge = 'dark';
                elseif (str_starts_with($status, 'On Leave')) $badge = 'info text-dark';
              @endphp
              <tr>
                <td>{{ \Carbon\Carbon::parse($r['date'])->format('D, M j, Y') }}</td>
                <td>{{ $r['time_in'] }}</td>
                <td>{{ $r['time_out'] }}</td>
                <td>{{ $r['ot_hours'] !== '' ? number_format((float)$r['ot_hours'], 2) : '—' }}</td>
                <td><span class="badge bg-{{ $badge }}">{{ $status }}</span></td>
                <td>{{ $r['late_hours'] !== '' ? number_format((float)$r['late_hours'], 2) : '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>
@endsection
