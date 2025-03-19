@extends('layouts.app')
@section('content')
<h2>Attendance List</h2>
<table>
    <tr><th>Employee</th><th>Date</th><th>Status</th><th>Shift</th><th>Actions</th></tr>
    @foreach($attendances as $attendance)
        <tr>
            <td>{{ $attendance->employee->name }}</td>
            <td>{{ $attendance->date }}</td>
            <td>{{ $attendance->status }}</td>
            <td>{{ $attendance->shift }}</td>
            <td><a href="{{ route('attendance.show', $attendance->id) }}">View</a></td>
        </tr>
    @endforeach
</table>
@endsection