@extends('layouts.app')

@section('content')
    <h2>Leave Requests</h2>
    <table class="table">
        <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        @foreach ($leaves as $leave)
        <tr>
            <td>{{ $leave->employee->user->name }}</td>
            <td>{{ ucfirst($leave->leave_type) }}</td>
            <td>{{ $leave->start_date }}</td>
            <td>{{ $leave->end_date }}</td>
            <td>{{ ucfirst($leave->status) }}</td>
            <td>
                @if($leave->status === 'pending')
                    <form action="{{ route('leaves.approve', $leave->id) }}" method="POST" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
@endsection