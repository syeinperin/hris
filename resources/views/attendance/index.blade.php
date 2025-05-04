@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-4">Attendance Records</h2>

  {{-- Filter/Search Form --}}
  <form action="{{ route('attendance.index') }}" method="GET" class="row g-2 mb-3 align-items-end">
    {{-- Free‑text search (by code or name) --}}
    <div class="col-md-4">
      <label class="form-label">Search Attendance</label>
      <input type="text"
             name="search"
             class="form-control"
             placeholder="Search Attendance..."
             value="{{ request('search') }}">
    </div>

    {{-- Employee dropdown --}}
    <div class="col-md-2">
      <label class="form-label">Employee Name</label>
      <select name="employee_name" class="form-select">
        <option value="">All Employee Name</option>
        @foreach($employees as $emp)
          <option value="{{ $emp->name }}"
            {{ request('employee_name') == $emp->name ? 'selected' : '' }}>
            {{ $emp->name }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Date dropdown --}}
    <div class="col-md-2">
      <label class="form-label">Date</label>
      @php
        // Collect unique dates from the current page of rows
        $dates = collect($attendances->items())
                  ->pluck('date')
                  ->unique()
                  ->sort()
                  ->values();
      @endphp
      <select name="date" class="form-select">
        <option value="">All Date</option>
        @foreach($dates as $d)
          <option value="{{ $d }}"
            {{ request('date') == $d ? 'selected' : '' }}>
            {{ $d }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Status dropdown --}}
    <div class="col-md-2">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">All Status</option>
        @foreach(['On Time','Late','Absent'] as $st)
          <option value="{{ $st }}"
            {{ request('status') == $st ? 'selected' : '' }}>
            {{ $st }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </form>

  {{-- Attendance Table --}}
  <table class="table table-bordered table-striped align-middle">
    <thead>
      <tr>
        <th style="width:1%">
          <input type="checkbox" id="selectAll">
        </th>
        <th>Employee Code</th>
        <th>Employee Name</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Date</th>
        <th>Status</th>
        <th>Delete</th>
      </tr>
    </thead>
    <tbody>
      @forelse($attendances as $row)
      <tr>
        <td>
          @if($row['id'])
            <input type="checkbox" name="selected[]" value="{{ $row['id'] }}">
          @endif
        </td>
        <td>{{ $row['employee_code'] }}</td>
        <td>{{ $row['employee_name'] }}</td>
        <td>{{ $row['time_in'] }}</td>
        <td>{{ $row['time_out'] }}</td>
        <td>{{ $row['date'] }}</td>
        <td>{{ $row['status'] }}</td>
        <td>
          @if($row['id'])
          <form action="{{ route('attendance.destroy', $row['id']) }}"
                method="POST"
                onsubmit="return confirm('Are you sure you want to delete this record?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
          </form>
          @endif
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="8" class="text-center">No attendance records found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  {{-- Pagination --}}
  <div class="d-flex justify-content-center">
    {{ $attendances->withQueryString()->links() }}
  </div>
</div>
@endsection

@section('scripts')
<script>
  // “Select All” checkbox
  document.getElementById('selectAll').addEventListener('change', function(){
    document.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => {
      cb.checked = this.checked;
    });
  });
</script>
@endsection