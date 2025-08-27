{{-- resources/views/employees/create.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Add Employee')

@push('styles')
<style>
  :root{
    --brand:#26264e; --brand-2:#3a3a84;
    --muted:#6b7380; --ring:#e8ebf6;
  }
  .page-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
  .page-head h2{margin:0;font-weight:800}
  .page-sub{color:var(--muted);margin-bottom:18px}
  .tabs{display:flex;gap:18px;border-bottom:1px solid var(--ring);margin-bottom:18px;flex-wrap:wrap}
  .tabs a{padding:10px 0;font-weight:700;color:#2563eb;text-decoration:none;border-bottom:3px solid transparent}
  .tabs a.active{color:#111827;border-bottom-color:#111827}
  .panel{background:#fff;border:1px solid #eef0f6;border-radius:14px;padding:16px;margin-bottom:18px}
  .stage{background:#0e1624;border-radius:12px;position:relative;overflow:hidden;border:1px dashed #2a3345}
  .stage video{width:100%;height:380px;object-fit:cover}
  .box{position:absolute;border:2px solid #60a5fa;border-radius:10px;pointer-events:none}
  .box-label{position:absolute;left:0;top:-22px;background:#60a5fa;color:#0b1020;font-size:12px;padding:2px 6px;border-radius:6px}
  .btn-k{display:inline-flex;align-items:center;gap:8px;font-weight:700;border-radius:12px;padding:10px 14px}
  .btn-outline-brand{border:2px solid var(--brand-2);color:var(--brand-2);background:#fff}
  .btn-brand{border:2px solid var(--brand-2);background:var(--brand-2);color:#fff}
  .btn-k[disabled]{opacity:.6;cursor:not-allowed}
  .thumb{background:#f6f8fe;border:1px dashed #dbe1ef;border-radius:12px;height:180px;display:flex;align-items:center;justify-content:center}
  .form-text-muted{color:var(--muted);font-size:.9rem}
  .autofilled { background:#f3f6ff; }
</style>
@endpush

@section('content')
<div class="container py-3">

  <div class="page-head">
    <div>
      <h2>Add Employee</h2>
      <div class="page-sub">Create the account, fill in details, and (optionally) capture a face template.</div>
    </div>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Back to list
    </a>
  </div>

  {{-- Errors --}}
  @if(session('error'))
    <div class="alert alert-danger mb-3">
      <strong>Error:</strong> {!! nl2br(e(session('error'))) !!}
    </div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger mb-3">
      <strong>Please fix the errors below:</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data" id="empCreateForm">
    @csrf

    {{-- Top tabs (Personal before Account; Personal active) --}}
    <nav class="tabs" id="tabs">
      <a href="#tab-face">Face</a>
      <a href="#tab-personal" class="active">Personal</a>
      <a href="#tab-account">Account</a>
      <a href="#tab-work">Work</a>
      <a href="#tab-benefits">Benefits</a>
      <a href="#tab-education">Education</a>
      <a href="#tab-employment">Employment History</a>
      <a href="#tab-refs">Character References</a>
      <a href="#tab-docs">Certificates & Docs</a>
    </nav>

    {{-- FACE --}}
    <section id="tab-face" class="tab-panel" hidden>
      <div class="row g-3">
        <div class="col-lg-7">
          <div class="panel">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <h5 class="mb-0">Live Camera</h5>
              <div class="form-text-muted">Models load from <code>{{ asset('face-models') }}</code></div>
            </div>

            <div class="stage" id="stage">
              <video id="video" autoplay muted playsinline></video>
              <!-- dynamic blue box is added by JS -->
            </div>

            <div class="mt-2 form-text-muted" id="camStatus">
              Click <strong>Start Camera</strong>, keep your face inside the blue box, then <strong>Capture</strong>.
            </div>

            <div class="mt-2 d-flex gap-2 flex-wrap">
              <button type="button" id="btnStart" class="btn-k btn-outline-brand">
                <i class="bi bi-camera-video"></i> Start Camera
              </button>
              <button type="button" id="btnCapture" class="btn-k btn-brand" disabled>
                <i class="bi bi-record-circle"></i> Capture
              </button>
            </div>

            {{-- Hidden fields to post together with the employee --}}
            <input type="hidden" name="face_descriptor" id="face_descriptor">
            <input type="hidden" name="face_image_base64" id="face_image_base64">
          </div>
        </div>

        <div class="col-lg-5">
          <div class="panel">
            <h5 class="mb-2">Preview</h5>
            <div class="thumb"><canvas id="facePreview" width="380" height="180"></canvas></div>
            <div class="mt-2 form-text-muted">
              We only save a 128-dimension face descriptor and a small preview for auditing.
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- PERSONAL (default visible) --}}
    <section id="tab-personal" class="tab-panel">
      <div class="panel">
        <div class="row g-3">
          <div class="col-md-4 form-floating">
            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                   placeholder="First Name *" value="{{ old('first_name') }}" required>
            <label>First Name *</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="middle_name" class="form-control @error('middle_name') is-invalid @enderror"
                   placeholder="Middle Name" value="{{ old('middle_name') }}">
            <label>Middle Name</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="last_name" id="last_name"
                   class="form-control @error('last_name') is-invalid @enderror"
                   placeholder="Last Name *" value="{{ old('last_name') }}" required>
            <label>Last Name *</label>
          </div>

          <div class="col-12"><strong>Current Address *</strong></div>
          <div class="col-12 form-floating">
            <input type="text" name="current_street_address"
                   class="form-control @error('current_street_address') is-invalid @enderror"
                   placeholder="Street Address *" value="{{ old('current_street_address') }}" required>
            <label>Street Address *</label>
          </div>

          <div class="col-md-4 form-floating">
            <select id="current_province" name="current_province"
                    class="form-select @error('current_province') is-invalid @enderror" required>
              <option value="" disabled {{ old('current_province')? '' : 'selected' }}>Province *</option>
              @foreach($philippineProvinces as $prov)
                <option value="{{ $prov }}" {{ old('current_province')==$prov?'selected':'' }}>{{ $prov }}</option>
              @endforeach
            </select>
            <label>Province *</label>
          </div>

          {{-- visible select (mirrors to hidden input) --}}
          <div class="col-md-4 form-floating">
            <select id="current_city_select"
                    class="form-select @error('current_city') is-invalid @enderror" required>
              <option value="" disabled selected>Select City / Municipality</option>
            </select>
            <label>City *</label>
            <input type="hidden" name="current_city" id="current_city" value="{{ old('current_city') }}">
          </div>

          <div class="col-md-4 form-floating">
            <input type="text" id="current_postal_code" name="current_postal_code"
                   class="form-control @error('current_postal_code') is-invalid @enderror"
                   placeholder="ZIP Code" value="{{ old('current_postal_code') }}">
            <label>ZIP Code</label>
          </div>

          {{-- Permanent Address removed --}}

          <div class="col-md-3 form-floating">
            <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
              <option value="" disabled {{ old('gender')? '' : 'selected' }}>Gender *</option>
              <option value="male"   {{ old('gender')=='male'?'selected':'' }}>Male</option>
              <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
              <option value="other"  {{ old('gender')=='other'?'selected':'' }}>Other</option>
            </select>
            <label>Gender *</label>
          </div>

          <div class="col-md-3 form-floating">
            <input type="date" name="dob" id="dob"
                   class="form-control @error('dob') is-invalid @enderror"
                   placeholder="Date of Birth *" value="{{ old('dob') }}" required>
            <label>Date of Birth *</label>
          </div>

          <div class="col-md-4">
            <label class="form-label">Profile Picture</label>
            <input type="file" name="profile_picture"
                   class="form-control @error('profile_picture') is-invalid @enderror">
          </div>

          <div class="col-md-4 form-floating">
            <input type="text" name="birth_place"
                   class="form-control @error('birth_place') is-invalid @enderror"
                   placeholder="Birth Place" value="{{ old('birth_place') }}">
            <label>Birth Place</label>
          </div>

          <div class="col-md-4 form-floating">
            <select name="civil_status" class="form-select @error('civil_status') is-invalid @enderror">
              <option value="">—</option>
              <option value="single"     {{ old('civil_status')=='single'     ? 'selected' : '' }}>Single</option>
              <option value="married"    {{ old('civil_status')=='married'    ? 'selected' : '' }}>Married</option>
              <option value="widowed"    {{ old('civil_status')=='widowed'    ? 'selected' : '' }}>Widowed</option>
              <option value="separated"  {{ old('civil_status')=='separated'  ? 'selected' : '' }}>Separated</option>
              <option value="other"      {{ old('civil_status')=='other'      ? 'selected' : '' }}>Other</option>
            </select>
            <label>Civil Status</label>
          </div>
        </div>
      </div>
    </section>

    {{-- ACCOUNT --}}
    <section id="tab-account" class="tab-panel" hidden>
      <div class="panel">
        <div class="row g-3 align-items-end">
          <div class="col-md-4 form-floating">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
              placeholder="Email *" value="{{ old('email') }}" required>
            <label>Email *</label>
          </div>

          {{-- Auto-generate password controls --}}
          <div class="col-md-3 form-floating">
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Password *" required>
            <label>Password *</label>
          </div>
          <div class="col-md-3 form-floating">
            <input type="password" name="password_confirmation" id="password_confirm"
                   class="form-control" placeholder="Confirm *" required>
            <label>Confirm *</label>
          </div>
          <div class="col-md-2">
            <div class="form-check mb-1">
              <input class="form-check-input" type="checkbox" id="autoPwd" checked>
              <label class="form-check-label" for="autoPwd">Auto-generate</label>
            </div>
            <small class="text-muted d-block">Default: <em>lastName + birthYear</em></small>
          </div>

          <div class="col-md-2 form-floating">
            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
              <option value="" disabled selected>-- Select Role --</option>
              @foreach($roles as $r)
                <option value="{{ $r }}" {{ old('role')==$r?'selected':'' }}>{{ ucfirst($r) }}</option>
              @endforeach
            </select>
            <label>Role *</label>
          </div>
        </div>
      </div>
    </section>

    {{-- WORK --}}
    <section id="tab-work" class="tab-panel" hidden>
      <div class="panel">
        <div class="row g-3">
          <div class="col-md-4 form-floating">
            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
              <option value="" disabled selected>Department *</option>
              @foreach($departments as $id=>$name)
                <option value="{{ $id }}" {{ old('department_id')==$id?'selected':'' }}>{{ $name }}</option>
              @endforeach
            </select>
            <label>Department *</label>
          </div>
          <div class="col-md-4 form-floating">
            <select name="designation_id" class="form-select @error('designation_id') is-invalid @enderror" required>
              <option value="" disabled selected>Designation *</option>
              @foreach($designations as $id=>$name)
                <option value="{{ $id }}" {{ old('designation_id')==$id?'selected':'' }}>{{ $name }}</option>
              @endforeach
            </select>
            <label>Designation *</label>
          </div>
          <div class="col-md-4 form-floating">
            <select name="schedule_id" class="form-select @error('schedule_id') is-invalid @enderror">
              <option value="">Schedule (optional)</option>
              @foreach($schedules as $id=>$name)
                <option value="{{ $id }}" {{ old('schedule_id')==$id?'selected':'' }}>{{ $name }}</option>
              @endforeach
            </select>
            <label>Schedule</label>
          </div>

          <div class="col-md-4 form-floating">
            <select name="employment_type" class="form-select @error('employment_type') is-invalid @enderror" required>
              <option value="" disabled selected>Employment Type *</option>
              @foreach($employmentTypes as $k=>$lbl)
                <option value="{{ $k }}" {{ old('employment_type')==$k?'selected':'' }}>{{ $lbl }}</option>
              @endforeach
            </select>
            <label>Employment Type *</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="date" name="employment_start_date"
                   class="form-control @error('employment_start_date') is-invalid @enderror"
                   placeholder="Start Date" value="{{ old('employment_start_date') }}">
            <label>Start Date</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="date" name="employment_end_date"
                   class="form-control @error('employment_end_date') is-invalid @enderror"
                   placeholder="End Date *" value="{{ old('employment_end_date') }}" required>
            <label>End Date *</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="fingerprint_id"
                   class="form-control @error('fingerprint_id') is-invalid @enderror"
                   placeholder="Fingerprint ID" value="{{ old('fingerprint_id') }}">
            <label>Fingerprint ID</label>
          </div>
        </div>
      </div>
    </section>

    {{-- BENEFITS --}}
    <section id="tab-benefits" class="tab-panel" hidden>
      <div class="panel">
        <div class="row g-3">
          <div class="col-md-4 form-floating">
            <input type="text" name="gsis_id_no" class="form-control @error('gsis_id_no') is-invalid @enderror"
                   placeholder="GSIS ID No." value="{{ old('gsis_id_no') }}">
            <label>GSIS ID No.</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="pagibig_id_no" class="form-control @error('pagibig_id_no') is-invalid @enderror"
                   placeholder="PAGIBIG ID No." value="{{ old('pagibig_id_no') }}">
            <label>PAGIBIG ID No.</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="philhealth_tin_id_no" class="form-control @error('philhealth_tin_id_no') is-invalid @enderror"
                   placeholder="PHILHEALTH TIN ID No." value="{{ old('philhealth_tin_id_no') }}">
            <label>PHILHEALTH TIN ID No.</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="sss_no" class="form-control @error('sss_no') is-invalid @enderror"
                   placeholder="SSS No." value="{{ old('sss_no') }}">
            <label>SSS No.</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="tin_no" class="form-control @error('tin_no') is-invalid @enderror"
                   placeholder="TIN No." value="{{ old('tin_no') }}">
            <label>TIN No.</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="agency_employee_no" class="form-control @error('agency_employee_no') is-invalid @enderror"
                   placeholder="Agency Emp. No." value="{{ old('agency_employee_no') }}">
            <label>Agency Emp. No.</label>
          </div>
        </div>
      </div>
    </section>

    {{-- EDUCATION --}}
    <section id="tab-education" class="tab-panel" hidden>
      <div class="panel">
        <div class="row g-3">
          <div class="col-12"><h6 class="fw-bold">🎓 Educational Background</h6></div>
          <div class="col-md-6 form-floating">
            <input type="text" name="elementary_school" class="form-control" placeholder="Elementary School" value="{{ old('elementary_school') }}">
            <label>Elementary School</label>
          </div>
          <div class="col-md-6 form-floating">
            <input type="number" name="elementary_year_graduated" class="form-control" placeholder="Year Graduated" value="{{ old('elementary_year_graduated') }}">
            <label>Year Graduated</label>
          </div>
          <div class="col-md-6 form-floating">
            <input type="text" name="high_school" class="form-control" placeholder="High School" value="{{ old('high_school') }}">
            <label>High School</label>
          </div>
          <div class="col-md-6 form-floating">
            <input type="number" name="high_school_year_graduated" class="form-control" placeholder="Year Graduated" value="{{ old('high_school_year_graduated') }}">
            <label>Year Graduated</label>
          </div>
          <div class="col-md-6 form-floating">
            <input type="text" name="college" class="form-control" placeholder="College" value="{{ old('college') }}">
            <label>College</label>
          </div>
          <div class="col-md-6 form-floating">
            <input type="number" name="college_year_graduated" class="form-control" placeholder="Year Graduated" value="{{ old('college_year_graduated') }}">
            <label>Year Graduated</label>
          </div>
          <div class="col-md-6 form-floating">
            <input type="text" name="degree_received" class="form-control" placeholder="Degree Received" value="{{ old('degree_received') }}">
            <label>Degree Received</label>
          </div>
          <div class="col-12 form-floating">
            <textarea name="special_skills" class="form-control" placeholder="Special Skills">{{ old('special_skills') }}</textarea>
            <label>Special Skills</label>
          </div>
        </div>
      </div>
    </section>

    {{-- EMPLOYMENT HISTORY --}}
    <section id="tab-employment" class="tab-panel" hidden>
      <div class="panel">
        <div class="row g-3">
          <div class="col-12"><h6 class="fw-bold">💼 Employment Record</h6></div>
          <div class="col-md-6 form-floating">
            <input type="text" name="emp1_company" class="form-control" placeholder="Company 1" value="{{ old('emp1_company') }}">
            <label>Company</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="emp1_position" class="form-control" placeholder="Position" value="{{ old('emp1_position') }}">
            <label>Position</label>
          </div>
          <div class="col-md-2 form-floating">
            <input type="date" name="emp1_from" class="form-control" placeholder="From" value="{{ old('emp1_from') }}">
            <label>From</label>
          </div>
          <div class="col-md-2 form-floating">
            <input type="date" name="emp1_to" class="form-control" placeholder="To" value="{{ old('emp1_to') }}">
            <label>To</label>
          </div>
          <div class="col-md-6 form-floating">
            <input type="text" name="emp2_company" class="form-control" placeholder="Company 2" value="{{ old('emp2_company') }}">
            <label>Company</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="emp2_position" class="form-control" placeholder="Position" value="{{ old('emp2_position') }}">
            <label>Position</label>
          </div>
          <div class="col-md-2 form-floating">
            <input type="date" name="emp2_from" class="form-control" placeholder="From" value="{{ old('emp2_from') }}">
            <label>From</label>
          </div>
          <div class="col-md-2 form-floating">
            <input type="date" name="emp2_to" class="form-control" placeholder="To" value="{{ old('emp2_to') }}">
            <label>To</label>
          </div>
        </div>
      </div>
    </section>

    {{-- REFERENCES --}}
    <section id="tab-refs" class="tab-panel" hidden>
      <div class="panel">
        <div class="row g-3">
          <div class="col-12"><h6 class="fw-bold">📝 Character References</h6></div>
          <div class="col-md-6 form-floating">
            <input type="text" name="char1_name" class="form-control" placeholder="Name" value="{{ old('char1_name') }}">
            <label>Name</label>
          </div>
          <div class="col-md-3 form-floating">
            <input type="text" name="char1_position" class="form-control" placeholder="Position" value="{{ old('char1_position') }}">
            <label>Position</label>
          </div>
          <div class="col-md-3 form-floating">
            <input type="text" name="char1_company" class="form-control" placeholder="Company" value="{{ old('char1_company') }}">
            <label>Company</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="char1_contact" class="form-control" placeholder="Contact" value="{{ old('char1_contact') }}">
            <label>Contact</label>
          </div>

          <div class="col-md-6 form-floating">
            <input type="text" name="char2_name" class="form-control" placeholder="Name" value="{{ old('char2_name') }}">
            <label>Name</label>
          </div>
          <div class="col-md-3 form-floating">
            <input type="text" name="char2_position" class="form-control" placeholder="Position" value="{{ old('char2_position') }}">
            <label>Position</label>
          </div>
          <div class="col-md-3 form-floating">
            <input type="text" name="char2_company" class="form-control" placeholder="Company" value="{{ old('char2_company') }}">
            <label>Company</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="char2_contact" class="form-control" placeholder="Contact" value="{{ old('char2_contact') }}">
            <label>Contact</label>
          </div>
        </div>
      </div>
    </section>

    {{-- CERTS --}}
    <section id="tab-docs" class="tab-panel" hidden>
      <div class="panel">
        <div class="row g-3">
          <div class="col-12"><h6 class="fw-bold">📑 Certificates & Docs</h6></div>
          <div class="col-md-4 form-floating">
            <input type="text" name="res_cert_no" class="form-control" placeholder="Res. Cert. No." value="{{ old('res_cert_no') }}">
            <label>Res. Cert. No.</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="res_cert_issued_at" class="form-control" placeholder="Issued At" value="{{ old('res_cert_issued_at') }}">
            <label>Issued At</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="date" name="res_cert_issued_on" class="form-control" placeholder="Issued On" value="{{ old('res_cert_issued_on') }}">
            <label>Issued On</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="nbi_no" class="form-control" placeholder="NBI No." value="{{ old('nbi_no') }}">
            <label>NBI No.</label>
          </div>
          <div class="col-md-4 form-floating">
            <input type="text" name="passport_no" class="form-control" placeholder="Passport No." value="{{ old('passport_no') }}">
            <label>Passport No.</label>
          </div>
        </div>
      </div>
    </section>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-success">
        <i class="bi bi-save2 me-1"></i> Save
      </button>
      <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script>
  // ─────────────────────────────────────────────────────────────
  // Tabs (Personal shown by default)
  // ─────────────────────────────────────────────────────────────
  const tabsNav = document.getElementById('tabs');
  const panels = Array.from(document.querySelectorAll('.tab-panel'));
  tabsNav.addEventListener('click', (e)=>{
    const a = e.target.closest('a'); if(!a) return;
    e.preventDefault();
    tabsNav.querySelectorAll('a').forEach(x=>x.classList.remove('active'));
    a.classList.add('active');
    const id = a.getAttribute('href');
    panels.forEach(p=> p.hidden = ('#'+p.id) !== id);
  });
  // ensure Personal is visible if server didn't set hidden flags
  panels.forEach(p=> p.hidden = (p.id !== 'tab-personal'));

  // ─────────────────────────────────────────────────────────────
  // Province → Cities (expanded lists) + ZIP auto-fill
  // ─────────────────────────────────────────────────────────────
  const CITY_MAP = {
    Cavite: [
      "Alfonso","Amadeo","Bacoor","Carmona","Cavite City","Dasmariñas","General Emilio Aguinaldo",
      "General Mariano Alvarez","General Trias","Imus","Indang","Kawit","Magallanes","Maragondon",
      "Mendez","Naic","Noveleta","Rosario","Silang","Tagaytay","Tanza","Ternate","Trece Martires"
    ],
    Laguna: [
      "Alaminos","Bay","Biñan","Cabuyao","Calamba","Calauan","Cavinti","Famy","Kalayaan","Liliw",
      "Los Baños","Luisiana","Lumban","Mabitac","Magdalena","Majayjay","Nagcarlan","Paete","Pagsanjan",
      "Pakil","Pangil","Pila","Rizal","San Pablo","San Pedro","Santa Cruz","Santa Maria","Santa Rosa",
      "Siniloan","Victora","Victoria"
    ],
    Batangas: [
      "Agoncillo","Alitagtag","Balayan","Balete","Batangas City","Bauan","Calaca","Calatagan","Cuenca",
      "Ibaan","Laurel","Lemery","Lian","Lipa","Lobo","Mabini","Malvar","Mataasnakahoy","Nasugbu","Padre Garcia",
      "Rosario","San Jose","San Juan","San Luis","San Nicolas","San Pascual","Santa Teresita","Santo Tomas",
      "Taal","Talisay","Tanauan","Taysan","Tingloy","Tuy"
    ],
    Rizal: [
      "Angono","Antipolo","Baras","Binangonan","Cainta","Cardona","Jalajala","Morong","Pililla","Rodriguez",
      "San Mateo","Tanay","Taytay","Teresa"
    ],
    Quezon: [
      "Agdangan","Alabat","Atimonan","Buenavista","Burdeos","Calauag","Candelaria","Catanauan","Dolores",
      "General Luna","General Nakar","Guinayangan","Gumaca","Infanta","Jomalig","Lopez","Lucban","Lucena",
      "Macalelon","Mauban","Mulanay","Padre Burgos","Pagbilao","Panukulan","Patnanungan","Perez","Plaridel",
      "Polillo","Quezon","Real","Sampaloc","San Andres","San Antonio","San Francisco","San Narciso","Sariaya",
      "Tagkawayan","Tayabas","Tiaong","Unisan"
    ]
  };

  // Minimal ZIP map (expand anytime)
  const POSTAL_MAP = {
    Cavite: {
      "Bacoor":"4102","Carmona":"4116","Cavite City":"4100","Dasmariñas":"4114","General Trias":"4107",
      "Imus":"4103","Kawit":"4104","Naic":"4110","Noveleta":"4105","Rosario":"4106","Silang":"4118",
      "Tagaytay":"4120","Tanza":"4108","Trece Martires":"4109","Alfonso":"4123","Amadeo":"4119",
      "Indang":"4122","Gen. Mariano Alvarez":"4117","General Mariano Alvarez":"4117","Magallanes":"4113",
      "Maragondon":"4112","Mendez":"4121","Ternate":"4111"
    },
    Laguna: {
      "Biñan":"4024","Cabuyao":"4025","Calamba":"4027","San Pablo":"4000","San Pedro":"4023",
      "Santa Cruz":"4009","Santa Rosa":"4026","Los Baños":"4030","Bay":"4033","Liliw":"4004",
      "Nagcarlan":"4002","Paete":"4016","Pagsanjan":"4008","Pila":"4010","Siniloan":"4019",
      "Majayjay":"4005","Cavinti":"4013","Famy":"4021","Kalayaan":"4015","Lumban":"4014",
      "Mabitac":"4020","Magdalena":"4007","Majayjay":"4005","Pakil":"4017","Pangil":"4018",
      "Santa Maria":"4022","Luisiana":"4032","Victoria":"4011","Victora":"4011"
    },
    Batangas: {
      "Batangas City":"4200","Lipa":"4217","Tanauan":"4232","Balayan":"4213","Bauan":"4201","Calaca":"4212",
      "Calatagan":"4215","Lemery":"4209","Nasugbu":"4231","Rosario":"4225","San Jose":"4227","San Juan":"4226",
      "Santo Tomas":"4234","Taal":"4208","Taysan":"4233","Lian":"4216","Agoncillo":"4211","Alitagtag":"4205",
      "Balete":"4219","Cuenca":"4222","Ibaan":"4230","Laurel":"4221","Lobo":"4216","Mabini":"4202",
      "Malvar":"4233","Mataasnakahoy":"4223","Padre Garcia":"4224","San Luis":"4210","San Nicolas":"4207",
      "San Pascual":"4204","Santa Teresita":"4206","Talisay":"4220","Tingloy":"4203","Tuy":"4214"
    },
    Rizal: {
      "Antipolo":"1870","Cainta":"1900","Taytay":"1920","Binangonan":"1940","Angono":"1930","Baras":"1970",
      "Cardona":"1950","Jalajala":"1990","Morong":"1960","Pililla":"1910","Rodriguez":"1860","San Mateo":"1850",
      "Tanay":"1980","Teresa":"1880"
    },
    Quezon: {
      "Lucena":"4301","Lucban":"4328","Tayabas":"4327","Sariaya":"4322","Tiaong":"4325","Candelaria":"4323",
      "Pagbilao":"4302","Atimonan":"4331","Gumaca":"4307","Lopez":"4316","Real":"4335","Mauban":"4330",
      "Infanta":"4336","General Nakar":"4338","Polillo":"4339","Quezon":"4332","Unisan":"4305","Tagkawayan":"4321",
      "Agdangan":"4304","Alabat":"4333","Buenavista":"4320","Burdeos":"4340","Calauag":"4318","Catanauan":"4311",
      "Dolores":"4326","General Luna":"4310","Guinayangan":"4319","Jomalig":"4341","Macalelon":"4309",
      "Mulanay":"4312","Padre Burgos":"4303","Panukulan":"4337","Patnanungan":"4342","Perez":"4334",
      "Plaridel":"4306","Sampaloc":"4329","San Andres":"4314","San Antonio":"4324","San Francisco":"4313",
      "San Narciso":"4315"
    }
  };

  const provinceSel = document.getElementById('current_province');
  const citySel = document.getElementById('current_city_select');
  const cityHidden = document.getElementById('current_city');
  const zipInput = document.getElementById('current_postal_code');

  function buildCityOptions(prov, selectedText){
    citySel.innerHTML = '<option value="" disabled>Select City / Municipality</option>';
    const list = CITY_MAP[prov] || [];
    list.forEach(txt=>{
      const opt = document.createElement('option');
      opt.value = txt;
      opt.textContent = txt;
      if(selectedText && selectedText === txt) opt.selected = true;
      citySel.appendChild(opt);
    });
    // mirror to hidden input
    cityHidden.value = citySel.value || selectedText || '';
    updatePostalCode();
  }

  function updatePostalCode(){
    const prov = provinceSel.value;
    const city = citySel.value || cityHidden.value;
    const zip = (POSTAL_MAP[prov] && POSTAL_MAP[prov][city]) ? POSTAL_MAP[prov][city] : '';
    if (zip) {
      zipInput.value = zip;
      zipInput.readOnly = true;
      zipInput.classList.add('autofilled');
    } else {
      if (!zipInput.value) zipInput.value = '';
      zipInput.readOnly = false;
      zipInput.classList.remove('autofilled');
    }
  }

  provinceSel.addEventListener('change', ()=> buildCityOptions(provinceSel.value, '') );
  citySel.addEventListener('change', ()=>{
    cityHidden.value = citySel.value;
    updatePostalCode();
  });

  // Initialize from old() values so validation redisplay works
  (function initCityFromOld(){
    const oldProv = "{{ old('current_province') }}";
    const oldCity = "{{ old('current_city') }}";
    if (oldProv) buildCityOptions(oldProv, oldCity);
    else updatePostalCode();
  })();

  // ─────────────────────────────────────────────────────────────
  // Age >= 18: limit date input and guard on submit
  // ─────────────────────────────────────────────────────────────
  const dobEl = document.getElementById('dob');
  (function setDobMax18(){
    const today = new Date();
    const max = new Date(today.getFullYear()-18, today.getMonth(), today.getDate());
    const iso = max.toISOString().slice(0,10);
    dobEl.setAttribute('max', iso);
  })();

  function isAdult(dateStr){
    if(!dateStr) return false;
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return false;
    const today = new Date();
    let age = today.getFullYear() - d.getFullYear();
    const m = today.getMonth() - d.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < d.getDate())) age--;
    return age >= 18;
  }

  document.getElementById('empCreateForm').addEventListener('submit', (e)=>{
    if (!isAdult(dobEl.value)) {
      e.preventDefault();
      dobEl.setCustomValidity('Employee must be at least 18 years old.');
      dobEl.reportValidity();
      // jump to Personal tab so the user sees the error
      tabsNav.querySelectorAll('a').forEach(x=>x.classList.remove('active'));
      tabsNav.querySelector('a[href="#tab-personal"]').classList.add('active');
      panels.forEach(p=> p.hidden = (p.id !== 'tab-personal'));
    } else {
      dobEl.setCustomValidity('');
    }
  });

  // ─────────────────────────────────────────────────────────────
  // Auto-generate password: lastName + birthYear
  // Checked => fill & lock, Unchecked => manual
  // ─────────────────────────────────────────────────────────────
  const lastNameEl = document.getElementById('last_name');
  const pwdEl  = document.getElementById('password');
  const pwd2El = document.getElementById('password_confirm');
  const autoPwd = document.getElementById('autoPwd');

  function nameNoSpaces(s){ return (s || '').trim().replace(/\s+/g,''); }
  function birthYearFromDOB(){
    const v = dobEl.value;
    if (!v) return '';
    const d = new Date(v);
    return isNaN(d.getTime()) ? '' : String(d.getFullYear());
  }
  function makePassword(){
    const ln = nameNoSpaces(lastNameEl.value);
    const yr = birthYearFromDOB();
    return (ln && yr) ? `${ln}${yr}` : '';
  }

  function applyAutoPassword(){
    if (autoPwd.checked){
      const gen = makePassword();
      pwdEl.value = gen;
      pwd2El.value = gen;
      pwdEl.readOnly = true;
      pwd2El.readOnly = true;
    } else {
      pwdEl.readOnly = false;
      pwd2El.readOnly = false;
    }
  }

  [lastNameEl, dobEl].forEach(el => el.addEventListener('input', ()=>{
    if (autoPwd.checked) applyAutoPassword();
  }));
  autoPwd.addEventListener('change', applyAutoPassword);
  document.addEventListener('DOMContentLoaded', applyAutoPassword);

  // Optional auto-jump to Account after Personal is sufficient
  let jumped = false;
  function maybeJumpToAccount(){
    if (!jumped && isAdult(dobEl.value) && nameNoSpaces(lastNameEl.value)){
      jumped = true;
      tabsNav.querySelectorAll('a').forEach(x=>x.classList.remove('active'));
      tabsNav.querySelector('a[href="#tab-account"]').classList.add('active');
      panels.forEach(p=> p.hidden = (p.id !== 'tab-account'));
    }
  }
  [lastNameEl, dobEl].forEach(el => el.addEventListener('input', maybeJumpToAccount));

  // ─────────────────────────────────────────────────────────────
  // Face capture (unchanged)
  // ─────────────────────────────────────────────────────────────
  const MODEL_URI = "{{ asset('face-models') }}";
  const video = document.getElementById('video');
  const stage = document.getElementById('stage');
  const btnStart = document.getElementById('btnStart');
  const btnCapture = document.getElementById('btnCapture');
  const camStatus = document.getElementById('camStatus');
  const prevCanvas = document.getElementById('facePreview');
  const faceDescInput = document.getElementById('face_descriptor');
  const faceImgInput  = document.getElementById('face_image_base64');

  let modelsLoaded = false;
  let boxEl = null;
  let stream = null;

  function ensureBox() {
    if (boxEl) return;
    boxEl = document.createElement('div');
    boxEl.className = 'box';
    const label = document.createElement('div');
    label.className = 'box-label';
    label.textContent = 'Face detected';
    boxEl.appendChild(label);
    stage.appendChild(boxEl);
  }
  function hideBox(){ if(boxEl) boxEl.style.display='none'; }
  function showBox(x,y,w,h){
    ensureBox();
    boxEl.style.display = 'block';
    boxEl.style.left = x+'px';
    boxEl.style.top = y+'px';
    boxEl.style.width = w+'px';
    boxEl.style.height = h+'px';
  }

  async function loadModels(){
    if (modelsLoaded) return;
    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URI);
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URI);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URI);
    modelsLoaded = true;
  }

  btnStart.addEventListener('click', async ()=>{
    try{
      await loadModels();
      stream = await navigator.mediaDevices.getUserMedia({ video:{ facingMode:'user' }, audio:false });
      video.srcObject = stream;
      camStatus.textContent = 'Camera ready. Keep your face within the box; press Capture.';
      btnCapture.disabled = false;

      const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 544, scoreThreshold: 0.35 });
      const drawLoop = async ()=>{
        if (!video.srcObject) return;
        const r = await faceapi.detectSingleFace(video, opts);
        if (!r) { hideBox(); requestAnimationFrame(drawLoop); return; }

        const vw = video.videoWidth, vh = video.videoHeight;
        const rw = stage.clientWidth, rh = 380;
        const sx = rw / vw, sy = rh / vh;
        const b = r.box;
        showBox(b.x*sx, b.y*sy, b.width*sx, b.height*sy);

        requestAnimationFrame(drawLoop);
      };
      requestAnimationFrame(drawLoop);
    }catch(e){
      camStatus.textContent = 'Cannot access camera: ' + e.message;
    }
  });

  btnCapture.addEventListener('click', async ()=>{
    try{
      await loadModels();
      if (!video.srcObject) { camStatus.textContent = 'Start the camera first.'; return; }

      const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 640, scoreThreshold: 0.32 });
      const det = await faceapi.detectSingleFace(video, opts).withFaceLandmarks().withFaceDescriptor();

      if (!det) {
        camStatus.textContent = 'No face detected. Move closer and face the camera.';
        return;
      }

      const ctx = prevCanvas.getContext('2d');
      prevCanvas.width = video.videoWidth; prevCanvas.height = 180;
      const scale = prevCanvas.height / video.videoHeight;
      const w = video.videoWidth * scale, h = video.videoHeight * scale;
      ctx.fillStyle = '#f6f8fe'; ctx.fillRect(0,0,prevCanvas.width,prevCanvas.height);
      ctx.drawImage(video, (prevCanvas.width-w)/2, 0, w, h);

      faceDescInput.value = JSON.stringify(Array.from(det.descriptor));
      faceImgInput.value  = prevCanvas.toDataURL('image/png');
      camStatus.textContent = 'Captured! The face template will be saved with this employee.';

    }catch(e){
      camStatus.textContent = 'Capture error: ' + e.message;
    }
  });
</script>
@endpush
