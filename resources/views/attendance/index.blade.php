@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Attendance Records</h2>

    <!-- 1. Filter/Search Form (GET) -->
    <form action="{{ route('attendance.index') }}" method="GET" class="row mb-3">
        <div class="col-md-3">
            <label for="employee_name" class="form-label">Employee Name</label>
            <input type="text" name="employee_name" id="employee_name" class="form-control"
                   value="{{ request('employee_name') }}" placeholder="Search by name...">
        </div>
        <div class="col-md-2">
            <label for="date_from" class="form-label">Date From</label>
            <input type="date" name="date_from" id="date_from" class="form-control"
                   value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label for="date_to" class="form-label">Date To</label>
            <input type="date" name="date_to" id="date_to" class="form-control"
                   value="{{ request('date_to') }}">
        </div>
        <div class="col-md-2">
            <label for="sort_by" class="form-label">Sort By</label>
            <select name="sort_by" id="sort_by" class="form-select">
                <option value="created_at" {{ request('sort_by')=='created_at' ? 'selected' : '' }}>Date</option>
                <option value="employee_name" {{ request('sort_by')=='employee_name' ? 'selected' : '' }}>Employee Name</option>
                <option value="time_in" {{ request('sort_by')=='time_in' ? 'selected' : '' }}>Time In</option>
                <option value="time_out" {{ request('sort_by')=='time_out' ? 'selected' : '' }}>Time Out</option>
            </select>
        </div>
        <div class="col-md-1">
            <label for="sort_order" class="form-label">Order</label>
            <select name="sort_order" id="sort_order" class="form-select">
                <option value="asc" {{ request('sort_order')=='asc' ? 'selected' : '' }}>ASC</option>
                <option value="desc" {{ request('sort_order')=='desc' ? 'selected' : '' }}>DESC</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- 2. Print Form (POST) -->
    <form action="{{ route('attendance.print') }}" method="POST" target="_blank">
        @csrf
        <!-- Keep filter values so the same subset can be printed -->
        <input type="hidden" name="employee_name" value="{{ request('employee_name') }}">
        <input type="hidden" name="date_from" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to" value="{{ request('date_to') }}">
        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
        <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">

        <!-- Print PDF button at the top of the table -->
        <div class="mb-2">
            <button type="submit" class="btn btn-success">Print PDF</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <!-- "Select All" checkbox -->
                    <th><input type="checkbox" id="select_all"></th>
                    <th>Employee ID</th>
                    <th>Employee Name</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                    <tr>
                        <td>
                            <input type="checkbox" name="selected_ids[]" value="{{ $attendance->id }}">
                        </td>
                        <td>{{ $attendance->employee_id }}</td>
                        <td>{{ $attendance->employee->name ?? 'N/A' }}</td>
                        <td>
                            @if ($attendance->time_in)
                                {{ \Carbon\Carbon::parse($attendance->time_in)->format('h:i:s A') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if ($attendance->time_out)
                                {{ \Carbon\Carbon::parse($attendance->time_out)->format('h:i:s A') }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $attendance->created_at->format('Y-m-d') }}</td>
                        <td>
                            @php
                                // Set default status to 'On Time'
                                $status = 'On Time';
                                
                                // If no time_in is recorded, mark as Absent
                                if (!$attendance->time_in) {
                                    $status = 'Absent';
                                }
                                // If there's a schedule, we compare actual and scheduled times
                                elseif ($attendance->schedule) {
                                    $scheduledTimeIn = \Carbon\Carbon::parse($attendance->schedule->time_in);
                                    $actualTimeIn = \Carbon\Carbon::parse($attendance->time_in);

                                    // Mark as Late if actual time_in is later than scheduled
                                    if ($actualTimeIn->greaterThan($scheduledTimeIn)) {
                                        $status = 'Late';
                                    }

                                    // Check if time_out exists and if actual time_out is later than scheduled
                                    if ($attendance->time_out) {
                                        $scheduledTimeOut = \Carbon\Carbon::parse($attendance->schedule->time_out);
                                        $actualTimeOut = \Carbon\Carbon::parse($attendance->time_out);

                                        if ($actualTimeOut->greaterThan($scheduledTimeOut)) {
                                            $status = 'Overtime';
                                        }
                                    }
                                }
                            @endphp
                            {{ $status }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No attendance records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </form>

    <!-- Pagination Links -->
    <div class="mt-3">
        {{ $attendances->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // "Select All" checkbox functionality
    const selectAll = document.getElementById('select_all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    }
});
</script>
@endsection
