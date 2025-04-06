@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Schedules</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('schedule.store') }}" class="mb-4">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <label>Name <small>(no spaces)</small></label>
                <input type="text" name="name" class="form-control" placeholder="e.g., Shift-One" required>
            </div>
            <div class="col-md-3">
                <label>Time In</label>
                <input type="time" name="time_in" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Time Out</label>
                <input type="time" name="time_out" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Save</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Shift</th>
                <th>Time In</th>
                <th>Time Out</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($schedules as $index => $schedule)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $schedule->name }}</td>
                <td>{{ $schedule->time_in }}</td>
                <td>{{ $schedule->time_out }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
