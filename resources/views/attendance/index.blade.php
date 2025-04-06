@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Attendance Records</h2>

    <!-- Display Success / Error Messages -->
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <!-- Employee ID from the attendances table -->
                    <td>{{ $attendance->employee_id }}</td>
                    <!-- Employee Name via relationship; display N/A if not found -->
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
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No attendance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
