@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Attendance Records</h2>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <!-- Filter/Search Form omitted for brevity... -->

    <!-- Attendance Records Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><input type="checkbox" id="select_all"></th>
                <th>Employee Code</th> <!-- Renamed from "Employee ID" -->
                <th>Employee Name</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Date</th>
                <th>Status</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td><input type="checkbox" name="selected_ids[]" value="{{ $attendance->id }}"></td>
                    <!-- Instead of $attendance->employee_id, show code: -->
                    <td>
                        {{ optional($attendance->employee)->employee_code ?? 'N/A' }}
                    </td>
                    <td>
                        {{ optional($attendance->employee)->name ?? 'N/A' }}
                    </td>
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
                            // Example status calculation
                            $status = 'On Time';
                            if (!$attendance->time_in) {
                                $status = 'Absent';
                            } elseif ($attendance->schedule) {
                                $scheduledTimeIn = \Carbon\Carbon::parse($attendance->schedule->time_in);
                                $actualTimeIn = \Carbon\Carbon::parse($attendance->time_in);
                                if ($actualTimeIn->greaterThan($scheduledTimeIn)) {
                                    $status = 'Late';
                                }
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
                    <td>
                        <!-- Delete Button -->
                        <form action="{{ route('attendance.destroy', $attendance->id) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this record?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No attendance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-3">
        {{ $attendances->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
