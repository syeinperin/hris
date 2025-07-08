@extends('layouts.app')

@section('page_title', 'Attendance for '.$employee->name)

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        Attendance: {{ $employee->name }}
      </h4>
      <form class="d-flex" method="GET" action="{{ route('attendance.show', ['attendance' => $employee->id]) }}">
        <input type="month" name="month" class="form-control form-control-sm me-2"
               value="{{ $month }}">
        <button class="btn btn-sm btn-primary">Go</button>
      </form>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Time In</th>
              <th>Time Out</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rows as $r)
              <tr>
                <td>{{ $r['date'] }}</td>
                <td>{{ $r['time_in'] }}</td>
                <td>{{ $r['time_out'] }}</td>
                <td>{{ $r['status'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <a href="{{ route('attendance.index') }}" class="btn btn-secondary mt-3">
        ← Back to Attendance List
      </a>
    </div>
  </div>
</div>
@endsection
