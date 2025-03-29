@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Attendance Records</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($attendances->isEmpty())
        <p>No attendance records found.</p>
    @else
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ $attendance->time_in }}</td>
                        <td>{{ $attendance->time_out ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
