@extends('layouts.app')

@section('page_title', 'Attendance List')

@push('styles')
<style>
  .table-sticky thead th {
    position: sticky; top: 0; z-index: 2;
  }
  .table-scroll { max-height: 65vh; overflow: auto; }
  .status-badge { font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid">

  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h4 class="mb-0">
        <i class="bi bi-clock-history me-2"></i> Attendance Records
      </h4>
      <div class="d-flex align-items-center gap-2">
        <a href="{{ route('payroll.calendar.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-calendar-event me-1"></i> Calendar
        </a>
        <a href="{{ route('holidays.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-flag me-1"></i> Holidays
        </a>
      </div>
    </div>

    <div class="card-body">

      {{-- Filters (keyword + start/end + status) --}}
      <x-search-bar
        :action="route('attendance.index')"
        placeholder="Search code or name…"
        :filters="[
          'status' => [
            '' => 'All Status',
            'On Time' => 'On Time',
            'Late' => 'Late',
            'Absent' => 'Absent',
            'Suspended' => 'Suspended',
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
              <th style="width:40px;">
                <input type="checkbox" id="selectAll">
              </th>
              <th style="min-width:120px;">Employee Code</th>
              <th style="min-width:220px;">Employee Name</th>
              <th style="min-width:120px;">Time In</th>
              <th style="min-width:120px;">Time Out</th>
              <th style="min-width:160px;">Date</th>
              <th style="min-width:140px;">Status</th>
              <th style="min-width:110px;">Late (hr)</th>
              <th style="min-width:90px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($attendances as $row)
              @php
                $status = (string) ($row['status'] ?? '');
                // Pick a bootstrap badge color based on status keywords
                $badge = 'secondary';
                if (str_starts_with($status, 'On Time'))     $badge = 'success';
                elseif (str_starts_with($status, 'Late'))     $badge = 'warning';
                elseif (str_starts_with($status, 'Absent'))   $badge = 'secondary';
                elseif (str_starts_with($status, 'Suspended'))$badge = 'dark';
                elseif (str_starts_with($status, 'On Leave')) $badge = 'info';
              @endphp
              <tr>
                <td>
                  @if(!empty($row['id']))
                    <input type="checkbox" name="selected[]" value="{{ $row['id'] }}">
                  @endif
                </td>
                <td class="fw-semibold">{{ $row['employee_code'] }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td>{{ $row['time_in'] }}</td>
                <td>{{ $row['time_out'] }}</td>
                <td>
                  {{ \Carbon\Carbon::parse($row['date'])->format('D, M d, Y') }}
                </td>
                <td>
                  <span class="badge status-badge bg-{{ $badge }}">{{ $status }}</span>
                </td>
                <td>
                  {{ $row['late_hours'] !== '' ? number_format((float)$row['late_hours'], 2) : '—' }}
                </td>
                <td>
                  {{-- Show per-employee month view. We pass "attendance" param (route-model name) as employee_id. --}}
                  <a
                    href="{{ route('attendance.show', [
                      'attendance' => $row['employee_id'],
                      'month'      => \Illuminate\Support\Str::substr($startDate, 0, 7)
                    ]) }}"
                    class="btn btn-sm btn-primary"
                    title="View month">
                    <i class="bi bi-eye"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">
                  <i class="bi bi-inbox me-1"></i> No attendance records found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-3">
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
      document.querySelectorAll('tbody input[type="checkbox"]').forEach(cb => {
        cb.checked = this.checked;
      });
    });
  }
</script>
@endpush
