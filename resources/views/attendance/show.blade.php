@extends('layouts.app')

@section('page_title')
  Attendance · {{ $employee->name }} ({{ \Carbon\Carbon::parse("$month-01")->format('F Y') }})
@endsection

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="bi bi-calendar3-week me-2"></i>
        {{ $employee->name }}’s Attendance
      </h4>
      <a href="{{ route('attendance.index', [
            'start_date' => $startOfMonth->toDateString(),
            'end_date'   => $endOfMonth->toDateString(),
            'search'     => $employee->employee_code
          ]) }}"
         class="btn btn-outline-secondary btn-sm">
        ← Back to List
      </a>
    </div>

    <div class="card-body p-0">
      <table class="table table-hover table-bordered mb-0">
        <thead class="table-light">
          <tr>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>OT (hr)</th>       {{-- new column --}}
            <th>Status</th>
            <th>Late (hr)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $r)
            <tr>
              <td>{{ \Carbon\Carbon::parse($r['date'])->format('M j, Y') }}</td>
              <td>{{ $r['time_in'] }}</td>
              <td>{{ $r['time_out'] }}</td>
              <td>
                {{ $r['ot_hours'] !== '' ? number_format($r['ot_hours'], 2) : '' }}
              </td>
              <td>{{ $r['status'] }}</td>
              <td>
                {{ $r['late_hours'] !== '' ? number_format($r['late_hours'], 2) : '' }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
