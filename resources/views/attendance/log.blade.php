{{-- resources/views/attendance/log.blade.php --}}
@extends('layouts.kiosk')

@section('page_title', 'Attendance Kiosk')

@section('content')
<div class="d-flex justify-content-center align-items-start vh-100">
    <div class="card mt-5" style="width: 380px;">
        <div class="card-body">
            {{-- Header: Title + Current Date + Live Clock --}}
            <div class="text-center mb-4">
                <h4 class="card-title">Attendance Kiosk</h4>
                <p class="text-muted mb-1">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</p>
                <p class="fs-2" id="live-time">{{ \Carbon\Carbon::now()->format('h:i:s A') }}</p>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger py-2">{{ session('error') }}</div>
            @endif

            {{-- Attendance Form --}}
            <form method="POST" action="{{ route('attendance.kiosk.post') }}">
                @csrf

                {{-- Hidden field to store “time_in” or “time_out” --}}
                <input type="hidden" name="attendance_type" id="attendance_type" value="">

                {{-- Employee Code (prefix “EMP”) --}}
                <div class="mb-3 input-group">
                    <span class="input-group-text">EMP</span>
                    <input
                      type="text"
                      name="employee_code"
                      id="employee_code"
                      class="form-control @error('employee_code') is-invalid @enderror"
                      placeholder="000"
                      value="{{ old('employee_code') }}"
                    >
                    @error('employee_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Employee Name (editable) --}}
                <div class="mb-4">
                    <input
                      type="text"
                      name="employee_name"
                      id="employee_name"
                      class="form-control @error('employee_name') is-invalid @enderror"
                      placeholder="Employee Name"
                      value="{{ old('employee_name') }}"
                    >
                    @error('employee_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Buttons: Time In / Time Out --}}
                <div class="d-grid gap-2 mb-3">
                    <button
                      type="button"
                      class="btn btn-success"
                      onclick="submitForm('time_in')"
                    >
                        <i class="bi bi-box-arrow-in-right"></i> Time In
                    </button>

                    <button
                      type="button"
                      class="btn btn-warning"
                      onclick="submitForm('time_out')"
                    >
                        <i class="bi bi-box-arrow-in-left"></i> Time Out
                    </button>
                </div>

                {{-- Return to Login --}}
                <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-left"></i> Return to Login
                </a>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 1) Live clock update every second
    setInterval(() => {
        const now = new Date();
        const opts = { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true };
        document.getElementById('live-time').textContent = now.toLocaleTimeString('en-US', opts);
    }, 1000);

    // 2) Submit form with hidden attendance_type
    function submitForm(type) {
        document.getElementById('attendance_type').value = type;
        document.querySelector('form').submit();
    }

    // 3) When Employee Code loses focus, fetch name via AJAX
    document.getElementById('employee_code').addEventListener('blur', function() {
        const codeValue = this.value.trim();
        if (! codeValue) {
            document.getElementById('employee_name').value = '';
            return;
        }

        fetch(`/attendance/employee/${codeValue}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('employee_name').value = data.name || '';
            })
            .catch(() => {
                document.getElementById('employee_name').value = '';
            });
    });

    // 4) When Employee Name loses focus, fetch code via AJAX
    document.getElementById('employee_name').addEventListener('blur', function() {
        const nameValue = this.value.trim();
        if (! nameValue) {
            document.getElementById('employee_code').value = '';
            return;
        }

        fetch(`/attendance/code/${encodeURIComponent(nameValue)}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('employee_code').value = data.code || '';
            })
            .catch(() => {
                document.getElementById('employee_code').value = '';
            });
    });
</script>
@endsection
