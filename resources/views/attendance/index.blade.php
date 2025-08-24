@extends('layouts.app')

@section('page_title', 'Attendance List')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i> Attendance Records</h4>
      <div>
        <a href="{{ route('payroll.calendar.index') }}"
           class="btn btn-outline-secondary btn-sm me-2">
          <i class="bi bi-calendar-event me-1"></i> Calendar
        </a>
        <a href="{{ route('holidays.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-flag me-1"></i> Holidays
        </a>
      </div>
    </div>

    <div class="card-body">

      {{-- Uniform Filters (keyword + start/end + status) --}}
      <x-search-bar
        :action="route('attendance.index')"
        placeholder="Search code or name…"
        :filters="[
          'status' => [
            '' => 'All Status',
            'On Time' => 'On Time',
            'Late' => 'Late',
            'Absent' => 'Absent',
          ],
        ]"
        :showDateRange="true"
        startName="start_date"
        endName="end_date"
      />

      {{-- Table --}}
      <div class="table-responsive table-scroll mb-3">
        <table class="table table-hover align-middle table-sticky">
          <thead class="table-light">
            <tr>
              <th><input type="checkbox" id="selectAll"></th>
              <th>Employee Code</th>
              <th>Employee Name</th>
              <th>Time In</th>
              <th>Time Out</th>
              <th>Date</th>
              <th>Status</th>
              <th>Late (hr)</th> {{-- new --}}
              <th>Action</th>
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
                  {{ $row['late_hours'] !== '' ? number_format($row['late_hours'], 2) : '' }}
                </td>
                <td>
                  <a href="{{ route('attendance.show', [
                        'attendance' => $row['employee_id'],
                        'month'      => \Illuminate\Support\Str::substr($startDate,0,7)
                      ]) }}"
                     class="btn btn-sm btn-primary">View</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center">No attendance records found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-4">
        <small class="text-muted">
          Showing {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }}
          of {{ $attendances->total() }}
        </small>
        {{ $attendances->withQueryString()->links('pagination::bootstrap-5') }}
      </div>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const selectAll = document.getElementById('selectAll');
  if (selectAll) {
    selectAll.addEventListener('change', function(){
      document.querySelectorAll('tbody input[type="checkbox"]').forEach(cb=>{
        cb.checked = this.checked;
      });
    });
  }
</script>
@endpush
