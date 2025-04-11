@extends('layouts.app')

@section('content')
<div class="container text-center" style="max-width: 400px; margin-top: 50px;">
    <!-- Live Clock Display -->
    <h2 id="liveClock" class="mb-4"></h2>

    <h3 class="mb-4">Attendance Kiosk</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Kiosk Attendance Form -->
    <form action="{{ route('attendance.log.submit') }}" method="POST" class="card p-3">
        @csrf

        <div class="mb-3">
            <label for="attendance_type" class="form-label">Select Action</label>
            <select name="attendance_type" id="attendance_type" class="form-control" required>
                <option value="time_in">Time In</option>
                <option value="time_out">Time Out</option>
            </select>
        </div>

        <!-- Renamed label and input field to Employee Code -->
        <div class="mb-3">
            <label for="employee_code" class="form-label">Employee Code</label>
            <input type="text" name="employee_code" id="employee_code" class="form-control" 
                   placeholder="Enter Employee Code" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
function updateClock() {
    var now = new Date();
    var options = { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true };
    var timeString = now.toLocaleString('en-US', options);
    document.getElementById('liveClock').textContent = timeString;
}
setInterval(updateClock, 1000);
updateClock();
</script>
@endsection
