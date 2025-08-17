{{-- resources/views/employees/create.blade.php --}}
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title" id="addEmployeeModalLabel">
            <i class="bi bi-person-plus-fill me-2"></i>Add Employee
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
          {{-- Flash & Validation Errors --}}
          @if(session('error'))
            <div class="alert alert-danger mb-3">
              <strong>Error:</strong> {!! nl2br(e(session('error'))) !!}
            </div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger mb-4">
              <strong>Please fix the errors below:</strong>
              <ul class="mb-0">
                @foreach($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Nav tabs --}}
          <ul class="nav nav-tabs mb-4" id="empCreateTab" role="tablist">
            <li class="nav-item"><button class="nav-link active"     data-bs-toggle="tab" data-bs-target="#create-account">Account</button></li>
            <li class="nav-item"><button class="nav-link"            data-bs-toggle="tab" data-bs-target="#create-personal">Personal</button></li>
            <li class="nav-item"><button class="nav-link"            data-bs-toggle="tab" data-bs-target="#create-work">Work</button></li>
            <li class="nav-item"><button class="nav-link"            data-bs-toggle="tab" data-bs-target="#create-benefits">Benefits</button></li>
            <li class="nav-item"><button class="nav-link"            data-bs-toggle="tab" data-bs-target="#create-education">Education</button></li>
            <li class="nav-item"><button class="nav-link"            data-bs-toggle="tab" data-bs-target="#create-employment">Employment History</button></li>
            <li class="nav-item"><button class="nav-link"            data-bs-toggle="tab" data-bs-target="#create-references">Character References</button></li>
            <li class="nav-item"><button class="nav-link"            data-bs-toggle="tab" data-bs-target="#create-certificates">Certificates & Docs</button></li>
          </ul>

          {{-- Tab panes --}}
          <div class="tab-content" id="empCreateTabContent">
            {{-- ACCOUNT --}}
            <div class="tab-pane fade show active" id="create-account" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                         placeholder="Email *" value="{{ old('email') }}" required>
                  <label>Email *</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                         placeholder="Password *" required>
                  <label>Password *</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="password" name="password_confirmation" class="form-control"
                         placeholder="Confirm *" required>
                  <label>Confirm *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="" disabled selected>-- Select Role --</option>
                    @foreach($roles as $r)
                      <option value="{{ $r }}" {{ old('role')==$r?'selected':'' }}>
                        {{ ucfirst($r) }}
                      </option>
                    @endforeach
                  </select>
                  <label>Role *</label>
                </div>
              </div>
            </div>

            {{-- PERSONAL --}}
            <div class="tab-pane fade" id="create-personal" role="tabpanel">
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
                  <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
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
                    <option value="" disabled selected>Province *</option>
                    @foreach($philippineProvinces as $prov)
                      <option value="{{ $prov }}" {{ old('current_province')==$prov?'selected':'' }}>
                        {{ $prov }}
                      </option>
                    @endforeach
                  </select>
                  <label>Province *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select id="current_city" name="current_city"
                          class="form-select @error('current_city') is-invalid @enderror" required>
                    <option value="" disabled selected>City / Municipality *</option>
                  </select>
                  <label>City *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" id="current_postal_code" name="current_postal_code"
                         class="form-control @error('current_postal_code') is-invalid @enderror"
                         placeholder="ZIP Code" readonly value="{{ old('current_postal_code') }}">
                  <label>ZIP Code</label>
                </div>

                <div class="col-md-6 form-floating">
                  <input type="text" name="permanent_address"
                         class="form-control @error('permanent_address') is-invalid @enderror"
                         placeholder="Permanent Address" value="{{ old('permanent_address') }}">
                  <label>Permanent Address</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                    <option value="" disabled selected>Gender *</option>
                    <option value="male"   {{ old('gender')=='male'?'selected':'' }}>Male</option>
                    <option value="female" {{ old('gender')=='female'?'selected':'' }}>Female</option>
                    <option value="other"  {{ old('gender')=='other'?'selected':'' }}>Other</option>
                  </select>
                  <label>Gender *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="date" name="dob"
                         class="form-control @error('dob') is-invalid @enderror"
                         placeholder="Date of Birth *" value="{{ old('dob') }}" required>
                  <label>Date of Birth *</label>
                </div>
                <div class="col-md-4">
                  <label>Profile Picture</label>
                  <input type="file" name="profile_picture"
                         class="form-control @error('profile_picture') is-invalid @enderror">
                </div>

                <div class="col-md-3 form-floating">
  <input
    type="text"
    name="birth_place"
    class="form-control @error('birth_place') is-invalid @enderror"
    placeholder="Birth Place"
    value="{{ old('birth_place') }}"
  >
  <label>Birth Place</label>
</div>

<div class="col-md-3 form-floating">
  <select
    name="civil_status"
    class="form-select @error('civil_status') is-invalid @enderror"
  >
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

            {{-- WORK --}}
            <div class="tab-pane fade" id="create-work" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <select name="department_id"
                          class="form-select @error('department_id') is-invalid @enderror" required>
                    <option value="" disabled selected>Department *</option>
                    @foreach($departments as $id=>$name)
                      <option value="{{ $id }}" {{ old('department_id')==$id?'selected':'' }}>
                        {{ $name }}
                      </option>
                    @endforeach
                  </select>
                  <label>Department *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="designation_id"
                          class="form-select @error('designation_id') is-invalid @enderror" required>
                    <option value="" disabled selected>Designation *</option>
                    @foreach($designations as $id=>$name)
                      <option value="{{ $id }}" {{ old('designation_id')==$id?'selected':'' }}>
                        {{ $name }}
                      </option>
                    @endforeach
                  </select>
                  <label>Designation *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="schedule_id"
                          class="form-select @error('schedule_id') is-invalid @enderror">
                    <option value="">Schedule (optional)</option>
                    @foreach($schedules as $id=>$name)
                      <option value="{{ $id }}" {{ old('schedule_id')==$id?'selected':'' }}>
                        {{ $name }}
                      </option>
                    @endforeach
                  </select>
                  <label>Schedule</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select name="employment_type"
                          class="form-select @error('employment_type') is-invalid @enderror" required>
                    <option value="" disabled selected>Employment Type *</option>
                    @foreach($employmentTypes as $k=>$lbl)
                      <option value="{{ $k }}" {{ old('employment_type')==$k?'selected':'' }}>
                        {{ $lbl }}
                      </option>
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

            {{-- BENEFITS --}}
            <div class="tab-pane fade" id="create-benefits" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="text" name="gsis_id_no"
                         class="form-control @error('gsis_id_no') is-invalid @enderror"
                         placeholder="GSIS ID No." value="{{ old('gsis_id_no') }}">
                  <label>GSIS ID No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="pagibig_id_no"
                         class="form-control @error('pagibig_id_no') is-invalid @enderror"
                         placeholder="PAGIBIG ID No." value="{{ old('pagibig_id_no') }}">
                  <label>PAGIBIG ID No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="philhealth_tin_id_no"
                         class="form-control @error('philhealth_tin_id_no') is-invalid @enderror"
                         placeholder="PHILHEALTH TIN ID No." value="{{ old('philhealth_tin_id_no') }}">
                  <label>PHILHEALTH TIN ID No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="sss_no"
                         class="form-control @error('sss_no') is-invalid @enderror"
                         placeholder="SSS No." value="{{ old('sss_no') }}">
                  <label>SSS No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="tin_no"
                         class="form-control @error('tin_no') is-invalid @enderror"
                         placeholder="TIN No." value="{{ old('tin_no') }}">
                  <label>TIN No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="agency_employee_no"
                         class="form-control @error('agency_employee_no') is-invalid @enderror"
                         placeholder="Agency Emp. No." value="{{ old('agency_employee_no') }}">
                  <label>Agency Emp. No.</label>
                </div>
              </div>
            </div>

            {{-- EDUCATION --}}
            <div class="tab-pane fade" id="create-education" role="tabpanel">
              <div class="row g-3">
                <div class="col-12"><h6 class="fw-bold">🎓 Educational Background</h6></div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="elementary_school" class="form-control"
                         placeholder="Elementary School" value="{{ old('elementary_school') }}">
                  <label>Elementary School</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="number" name="elementary_year_graduated" class="form-control"
                         placeholder="Year Graduated" value="{{ old('elementary_year_graduated') }}">
                  <label>Year Graduated</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="high_school" class="form-control"
                         placeholder="High School" value="{{ old('high_school') }}">
                  <label>High School</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="number" name="high_school_year_graduated" class="form-control"
                         placeholder="Year Graduated" value="{{ old('high_school_year_graduated') }}">
                  <label>Year Graduated</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="college" class="form-control"
                         placeholder="College" value="{{ old('college') }}">
                  <label>College</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="number" name="college_year_graduated" class="form-control"
                         placeholder="Year Graduated" value="{{ old('college_year_graduated') }}">
                  <label>Year Graduated</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="degree_received" class="form-control"
                         placeholder="Degree Received" value="{{ old('degree_received') }}">
                  <label>Degree Received</label>
                </div>
                <div class="col-12 form-floating">
                  <textarea name="special_skills" class="form-control"
                            placeholder="Special Skills">{{ old('special_skills') }}</textarea>
                  <label>Special Skills</label>
                </div>
              </div>
            </div>

            {{-- EMPLOYMENT HISTORY --}}
            <div class="tab-pane fade" id="create-employment" role="tabpanel">
              <div class="row g-3">
                <div class="col-12"><h6 class="fw-bold">💼 Employment Record</h6></div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="emp1_company" class="form-control"
                         placeholder="Company 1" value="{{ old('emp1_company') }}">
                  <label>Company</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="emp1_position" class="form-control"
                         placeholder="Position" value="{{ old('emp1_position') }}">
                  <label>Position</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="date" name="emp1_from" class="form-control"
                         placeholder="From" value="{{ old('emp1_from') }}">
                  <label>From</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="date" name="emp1_to" class="form-control"
                         placeholder="To" value="{{ old('emp1_to') }}">
                  <label>To</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="emp2_company" class="form-control"
                         placeholder="Company 2" value="{{ old('emp2_company') }}">
                  <label>Company</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="emp2_position" class="form-control"
                         placeholder="Position" value="{{ old('emp2_position') }}">
                  <label>Position</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="date" name="emp2_from" class="form-control"
                         placeholder="From" value="{{ old('emp2_from') }}">
                  <label>From</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="date" name="emp2_to" class="form-control"
                         placeholder="To" value="{{ old('emp2_to') }}">
                  <label>To</label>
                </div>
              </div>
            </div>

            {{-- CHARACTER REFERENCES --}}
            <div class="tab-pane fade" id="create-references" role="tabpanel">
              <div class="row g-3">
                <div class="col-12"><h6 class="fw-bold">📝 Character References</h6></div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="char1_name" class="form-control"
                         placeholder="Name" value="{{ old('char1_name') }}">
                  <label>Name</label>
                </div>
                <div class="col-md-3 form-floating">
                  <input type="text" name="char1_position" class="form-control"
                         placeholder="Position" value="{{ old('char1_position') }}">
                  <label>Position</label>
                </div>
                <div class="col-md-3 form-floating">
                  <input type="text" name="char1_company" class="form-control"
                         placeholder="Company" value="{{ old('char1_company') }}">
                  <label>Company</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="char1_contact" class="form-control"
                         placeholder="Contact" value="{{ old('char1_contact') }}">
                  <label>Contact</label>
                </div>

                <div class="col-md-6 form-floating">
                  <input type="text" name="char2_name" class="form-control"
                         placeholder="Name" value="{{ old('char2_name') }}">
                  <label>Name</label>
                </div>
                <div class="col-md-3 form-floating">
                  <input type="text" name="char2_position" class="form-control"
                         placeholder="Position" value="{{ old('char2_position') }}">
                  <label>Position</label>
                </div>
                <div class="col-md-3 form-floating">
                  <input type="text" name="char2_company" class="form-control"
                         placeholder="Company" value="{{ old('char2_company') }}">
                  <label>Company</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="char2_contact" class="form-control"
                         placeholder="Contact" value="{{ old('char2_contact') }}">
                  <label>Contact</label>
                </div>
              </div>
            </div>

            {{-- CERTIFICATES & DOCS --}}
            <div class="tab-pane fade" id="create-certificates" role="tabpanel">
              <div class="row g-3">
                <div class="col-12"><h6 class="fw-bold">📑 Certificates & Docs</h6></div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="res_cert_no" class="form-control"
                         placeholder="Res. Cert. No." value="{{ old('res_cert_no') }}">
                  <label>Res. Cert. No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="res_cert_issued_at" class="form-control"
                         placeholder="Issued At" value="{{ old('res_cert_issued_at') }}">
                  <label>Issued At</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="date" name="res_cert_issued_on" class="form-control"
                         placeholder="Issued On" value="{{ old('res_cert_issued_on') }}">
                  <label>Issued On</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="nbi_no" class="form-control"
                         placeholder="NBI No." value="{{ old('nbi_no') }}">
                  <label>NBI No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="passport_no" class="form-control"
                         placeholder="Passport No." value="{{ old('passport_no') }}">
                  <label>Passport No.</label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save2 me-1"></i> Save
          </button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
