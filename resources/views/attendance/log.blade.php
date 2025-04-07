@extends('layouts.app')

@section('content')
<div class="container text-center" style="max-width: 400px; margin-top: 50px;">
    <!-- Live Clock Display -->
    <h2 id="liveClock" class="mb-4"></h2>

    <!-- Kiosk Heading -->
    <h3 class="mb-4">Attendance Kiosk</h3>

    <!-- Success and Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Attendance Form -->
    <form action="{{ route('attendance.log.submit') }}" method="POST" class="card p-3">
        @csrf

        <div class="mb-3">
            <label for="attendance_type" class="form-label">Select Action</label>
            <select name="attendance_type" id="attendance_type" class="form-control" required>
                <option value="time_in">Time In</option>
                <option value="time_out">Time Out</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="employee_id" class="form-label">Employee ID</label>
            <input type="number" name="employee_id" id="employee_id" class="form-control" placeholder="Enter Employee ID" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
// Live Clock Function
function updateClock() {
    // Create a new Date object (browser local time)
    var now = new Date();
    // Format as a 12-hour clock with AM/PM; adjust as needed
    var options = { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true };
    var timeString = now.toLocaleString('en-US', options);
    document.getElementById('liveClock').textContent = timeString;
}
// Update clock every second
setInterval(updateClock, 1000);
updateClock();
</script>
@endsection
