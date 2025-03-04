@extends('layouts.app')

@section('content')
    <h2>Attendance Records</h2>
    <table class="table">
        <tr>
            <th>Employee</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Status</th>
        </tr>
        @foreach ($attendance as $record)
        <tr>
            <td>{{ $record->employee->user->name }}</td>
            <td>{{ $record->check_in }}</td>
            <td>{{ $record->check_out ?? 'Not Checked Out' }}</td>
            <td>{{ ucfirst($record->status) }}</td>
        </tr>
        @endforeach
    </table>
@endsection