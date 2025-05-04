@extends('layouts.kiosk')

@section('page_title','Attendance Kiosk')

@section('content')
<div class="card mx-auto mt-5" style="max-width:24rem;">
  <div class="card-header text-center bg-white">
    <h5>Attendance Kiosk</h5>
    <div id="current-date" class="text-muted small"></div>
    <div id="current-time" class="h4 fw-bold"></div>
  </div>

  <div class="card-body">
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('attendance.kiosk.post') }}" method="POST" class="row g-3">
      @csrf

      {{-- Employee Code suffix + hidden full code --}}
      <div class="col-12">
        <label for="code_suffix" class="form-label">Employee Code</label>
        <div class="input-group">
          <span class="input-group-text">EMP</span>
          <input
            type="text"
            id="code_suffix"
            class="form-control"
            placeholder="541"
            autocomplete="off"
            required
          >
        </div>
        <input type="hidden" name="employee_code" id="employee_code">
      </div>

      {{-- Employee Name --}}
      <div class="col-12 form-floating">
        <input
          type="text"
          name="employee_name"
          id="employee_name"
          class="form-control"
          placeholder="Employee Name"
          autocomplete="off"
          required
        >
        <label for="employee_name">Employee Name</label>
      </div>

      <div class="col-6">
        <button type="submit" name="attendance_type" value="time_in"
                class="btn btn-success w-100">
          <i class="bi bi-box-arrow-in-right me-1"></i>Time In
        </button>
      </div>
      <div class="col-6">
        <button type="submit" name="attendance_type" value="time_out"
                class="btn btn-warning w-100">
          <i class="bi bi-box-arrow-left me-1"></i>Time Out
        </button>
      </div>
    </form>
  </div>

  <div class="card-footer text-center bg-white">
    <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">
      <i class="bi bi-box-arrow-in-right me-1"></i>Return to Login
    </a>
  </div>
</div>
@endsection

@section('scripts')
<script>
  // Live date & time
  function updateDateTime() {
    const now = new Date();
    document.getElementById('current-date').textContent =
      now.toLocaleDateString(undefined, {
        weekday:'long', year:'numeric', month:'long', day:'numeric'
      });
    document.getElementById('current-time').textContent =
      now.toLocaleTimeString(undefined, {
        hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true
      });
  }
  updateDateTime();
  setInterval(updateDateTime, 1000);

  // Elements
  const codeSuffix      = document.getElementById('code_suffix');
  const hiddenCodeInput = document.getElementById('employee_code');
  const nameInput       = document.getElementById('employee_name');
  const csrfToken       = document.querySelector('meta[name="csrf-token"]').content;

  // Build and sync hidden employee_code (EMP + suffix)
  function syncHiddenCode() {
    const s = codeSuffix.value.trim();
    hiddenCodeInput.value = s ? 'EMP' + s : '';
  }

  // On code_suffix blur → fetch employee name
  codeSuffix.addEventListener('blur', () => {
    syncHiddenCode();
    const code = hiddenCodeInput.value;
    if (!code) {
      nameInput.value = '';
      return;
    }
    fetch(`/attendance/employee/${encodeURIComponent(code)}`, {
      headers: { 'X-CSRF-TOKEN': csrfToken }
    })
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(data => { nameInput.value = data.name || '' })
      .catch(() => { nameInput.value = '' });
  });

  // On nameInput blur → fetch code_suffix
  nameInput.addEventListener('blur', () => {
    const nm = nameInput.value.trim();
    if (!nm) {
      codeSuffix.value = '';
      syncHiddenCode();
      return;
    }
    fetch(`/attendance/code/${encodeURIComponent(nm)}`, {
      headers: { 'X-CSRF-TOKEN': csrfToken }
    })
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(data => {
        if (data.code) {
          // data.code is like "EMP541", strip "EMP" → "541"
          codeSuffix.value = data.code.replace(/^EMP/, '');
          syncHiddenCode();
        }
      })
      .catch(() => {});
  });
</script>
@endsection
