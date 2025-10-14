{{-- resources/views/employees/partials/edit-modal.blade.php --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="editEmployeeForm"
            method="POST"
            action="{{ route('employees.update', ['employee' => 0]) }}"
            enctype="multipart/form-data"
            novalidate>
        @csrf
        @method('PUT')

        <div class="modal-header">
          <h5 class="modal-title" id="editEmployeeModalLabel">
            <i class="bi bi-pencil-square me-2"></i>
            Edit <span class="js-emp-code">Employee</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body" style="max-height:calc(100vh - 200px);overflow-y:auto;">
          {{-- Tabs --}}
          <ul class="nav nav-tabs mb-4" id="empEditTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-edit-account" type="button">Account</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-personal" type="button">Personal</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-work" type="button">Work</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-benefits" type="button">Benefits</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-education" type="button">Education</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-emphistory" type="button">Employment History</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-characters" type="button">Character References</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-docs" type="button">Documents</button></li>
          </ul>

          <div class="tab-content" id="empEditTabContent">

            {{-- ACCOUNT --}}
            <div class="tab-pane fade show active" id="tab-edit-account">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="email" name="email" class="form-control" required>
                  <label>Email *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="role" class="form-select" required>
                    @foreach($roles as $r)
                      <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                    @endforeach
                  </select>
                  <label>Role *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                  </select>
                  <label>Status *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="password" name="password" class="form-control">
                  <label>New Password</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="password" name="password_confirmation" class="form-control">
                  <label>Confirm</label>
                </div>
              </div>
            </div>

            {{-- PERSONAL --}}
            <div class="tab-pane fade" id="tab-edit-personal">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="text" name="first_name" class="form-control" required>
                  <label>First Name *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="middle_name" class="form-control">
                  <label>Middle Name</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="last_name" class="form-control" required>
                  <label>Last Name *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="gender" class="form-select" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                  <label>Gender *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="date" name="dob" class="form-control" required>
                  <label>Date of Birth *</label>
                </div>

                {{-- Profile Picture --}}
                <div class="col-md-4">
                  <label class="form-label fw-bold">Profile Picture</label>
                  <div class="d-flex gap-2 flex-wrap">
                    <input type="file" name="profile_picture" class="form-control flex-grow-1" accept="image/*">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cameraModalEdit">
                      <i class="bi bi-camera-video"></i> Use Camera
                    </button>
                  </div>
                </div>

                {{-- Address --}}
                <div class="col-12"><h6 class="fw-bold mb-0">Current Address</h6></div>
                <div class="col-md-12 form-floating">
                  <input type="text" name="current_street_address" class="form-control" required>
                  <label>Street Address *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select id="current_province" name="current_province" class="form-select" required>
                    <option value="">Province *</option>
                    @foreach($philippineProvinces as $prov)
                      <option value="{{ $prov }}">{{ $prov }}</option>
                    @endforeach
                  </select>
                  <label>Province *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select id="current_city_select" class="form-select" required>
                    <option value="">Select City / Municipality</option>
                  </select>
                  <input type="hidden" name="current_city" id="current_city">
                  <label>City *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select id="current_barangay_select" class="form-select" required>
                    <option value="">Select Barangay</option>
                  </select>
                  <input type="hidden" name="current_barangay" id="current_barangay">
                  <label>Barangay *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <input type="text" name="current_postal_code" id="current_postal_code" class="form-control">
                  <label>ZIP Code</label>
                </div>

                <div class="col-md-8 form-floating">
                  <input type="text" name="permanent_address" class="form-control">
                  <label>Permanent Address</label>
                </div>
              </div>
            </div>

            {{-- WORK --}}
            <div class="tab-pane fade" id="tab-edit-work">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <select name="department_id" class="form-select" required>
                    @foreach($departments as $id => $name)
                      <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                  </select>
                  <label>Department *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="designation_id" class="form-select" required>
                    @foreach($designations as $id => $name)
                      <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                  </select>
                  <label>Designation *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="schedule_id" class="form-select">
                    <option value="">-- Optional --</option>
                    @foreach($schedules as $id => $name)
                      <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                  </select>
                  <label>Schedule</label>
                </div>
                <div class="col-md-4 form-floating">
                  <select name="employment_type" class="form-select" required>
                    @foreach($employmentTypes as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <label>Employment Type *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="date" name="employment_start_date" class="form-control">
                  <label>Start Date</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="date" name="employment_end_date" class="form-control">
                  <label>End Date</label>
                </div>
              </div>
            </div>

            {{-- BENEFITS --}}
            <div class="tab-pane fade" id="tab-edit-benefits">
              <div class="row g-3">
                <div class="col-md-4 form-floating"><input type="text" name="gsis_id_no" class="form-control"><label>GSIS ID No.</label></div>
                <div class="col-md-4 form-floating"><input type="text" name="pagibig_id_no" class="form-control"><label>PAGIBIG ID No.</label></div>
                <div class="col-md-4 form-floating"><input type="text" name="philhealth_tin_id_no" class="form-control"><label>PHILHEALTH TIN ID No.</label></div>
                <div class="col-md-4 form-floating"><input type="text" name="sss_no" class="form-control"><label>SSS No.</label></div>
                <div class="col-md-4 form-floating"><input type="text" name="tin_no" class="form-control"><label>TIN No.</label></div>
                <div class="col-md-4 form-floating"><input type="text" name="agency_employee_no" class="form-control"><label>Agency Employee No.</label></div>
              </div>
            </div>

            {{-- EDUCATION --}}
            <div class="tab-pane fade" id="tab-edit-education">
              <div class="row g-3">
                <div class="col-md-6 form-floating"><input type="text" name="elementary_school" class="form-control"><label>Elementary</label></div>
                <div class="col-md-6 form-floating"><input type="text" name="elementary_year_graduated" class="form-control"><label>Year Graduated</label></div>
                <div class="col-md-6 form-floating"><input type="text" name="high_school" class="form-control"><label>High School</label></div>
                <div class="col-md-6 form-floating"><input type="text" name="high_school_year_graduated" class="form-control"><label>Year Graduated</label></div>
                <div class="col-md-6 form-floating"><input type="text" name="college" class="form-control"><label>College</label></div>
                <div class="col-md-6 form-floating"><input type="text" name="college_year_graduated" class="form-control"><label>Year Graduated</label></div>
                <div class="col-md-6 form-floating"><input type="text" name="degree_received" class="form-control"><label>Degree Received</label></div>
                <div class="col-md-6 form-floating"><input type="text" name="special_skills" class="form-control"><label>Special Skills</label></div>
              </div>
            </div>

            {{-- EMPLOYMENT HISTORY --}}
            <div class="tab-pane fade" id="tab-edit-emphistory">
              <div class="row g-3">
                <div class="col-md-4 form-floating"><input type="text" name="emp1_company" class="form-control"><label>Company (1)</label></div>
                <div class="col-md-4 form-floating"><input type="text" name="emp1_position" class="form-control"><label>Position (1)</label></div>
                <div class="col-md-2 form-floating"><input type="date" name="emp1_from" class="form-control"><label>From (1)</label></div>
                <div class="col-md-2 form-floating"><input type="date" name="emp1_to" class="form-control"><label>To (1)</label></div>
                <div class="col-md-4 form-floating"><input type="text" name="emp2_company" class="form-control"><label>Company (2)</label></div>
                <div class="col-md-4 form-floating"><input type="text" name="emp2_position" class="form-control"><label>Position (2)</label></div>
                <div class="col-md-2 form-floating"><input type="date" name="emp2_from" class="form-control"><label>From (2)</label></div>
                <div class="col-md-2 form-floating"><input type="date" name="emp2_to" class="form-control"><label>To (2)</label></div>
              </div>
            </div>

            {{-- CHARACTER REFERENCES --}}
            <div class="tab-pane fade" id="tab-edit-characters">
              <div class="row g-3">
                <div class="col-md-3 form-floating"><input type="text" name="char1_name" class="form-control"><label>Name (1)</label></div>
                <div class="col-md-3 form-floating"><input type="text" name="char1_position" class="form-control"><label>Position (1)</label></div>
                <div class="col-md-3 form-floating"><input type="text" name="char1_company" class="form-control"><label>Company (1)</label></div>
                <div class="col-md-3 form-floating"><input type="text" name="char1_contact" class="form-control"><label>Contact (1)</label></div>
                <div class="col-md-3 form-floating"><input type="text" name="char2_name" class="form-control"><label>Name (2)</label></div>
                <div class="col-md-3 form-floating"><input type="text" name="char2_position" class="form-control"><label>Position (2)</label></div>
                <div class="col-md-3 form-floating"><input type="text" name="char2_company" class="form-control"><label>Company (2)</label></div>
                <div class="col-md-3 form-floating"><input type="text" name="char2_contact" class="form-control"><label>Contact (2)</label></div>
              </div>
            </div>

            {{-- DOCUMENTS --}}
            <div class="tab-pane fade" id="tab-edit-docs">
              <div class="row g-3">
                <div class="col-lg-6">
                  <label class="form-label fw-semibold">Resume</label>
                  <input type="file" name="resume_file" class="form-control" accept=".pdf,.doc,.docx,image/*">
                  <div class="form-text">Current:
                    <a id="em-cur-resume" href="#" target="_blank" class="d-none">Open</a>
                    <span id="em-cur-resume-none" class="text-muted">none</span>
                  </div>
                </div>
                <div class="col-lg-6">
                  <label class="form-label fw-semibold">MDR – PhilHealth</label>
                  <input type="file" name="mdr_philhealth_file" class="form-control" accept=".pdf,image/*">
                  <div class="form-text">Current:
                    <a id="em-cur-mdr-ph" href="#" target="_blank" class="d-none">Open</a>
                    <span id="em-cur-mdr-ph-none" class="text-muted">none</span>
                  </div>
                </div>
                <div class="col-lg-6">
                  <label class="form-label fw-semibold">MDR – SSS</label>
                  <input type="file" name="mdr_sss_file" class="form-control" accept=".pdf,image/*">
                  <div class="form-text">Current:
                    <a id="em-cur-mdr-sss" href="#" target="_blank" class="d-none">Open</a>
                    <span id="em-cur-mdr-sss-none" class="text-muted">none</span>
                  </div>
                </div>
                <div class="col-lg-6">
                  <label class="form-label fw-semibold">MDR – Pag-IBIG</label>
                  <input type="file" name="mdr_pagibig_file" class="form-control" accept=".pdf,image/*">
                  <div class="form-text">Current:
                    <a id="em-cur-mdr-pagibig" href="#" target="_blank" class="d-none">Open</a>
                    <span id="em-cur-mdr-pagibig-none" class="text-muted">none</span>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Medical Documents</label>
                  <input type="file" name="medical_documents[]" class="form-control" accept=".pdf,image/*" multiple>
                  <div class="form-text">
                    Current:
                    <span id="em-cur-med-none" class="text-muted">none</span>
                    <ul id="em-cur-med-list" class="mb-0"></ul>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save2 me-1"></i> Update</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('editEmployeeModal');
  const form = modalEl.querySelector('#editEmployeeForm');
  const baseEmployeesUrl = @json(url('/employees'));

  // --- Restore employee data ---
  modalEl.addEventListener('show.bs.modal', (evt) => {
    const btn = evt.relatedTarget;
    let emp = {};
    try { emp = JSON.parse(btn.getAttribute('data-employee') || '{}'); } catch(_) {}

    if (emp.id) form.action = `${baseEmployeesUrl}/${emp.id}`;
    modalEl.querySelector('.js-emp-code').textContent = emp.employee_code || 'Employee';

    // Fill basic fields
    ['email','first_name','middle_name','last_name','dob','current_street_address',
     'current_postal_code','permanent_address'].forEach(f => { if (form[f]) form[f].value = emp[f] || ''; });

    if (form['role']) form['role'].value = emp.role || '';
    if (form['status']) form['status'].value = emp.status || '';
    if (form['gender']) form['gender'].value = emp.gender || '';
    if (form['employment_type']) form['employment_type'].value = emp.employment_type || '';
    if (form['department_id']) form['department_id'].value = emp.department_id || '';
    if (form['designation_id']) form['designation_id'].value = emp.designation_id || '';
    if (form['schedule_id']) form['schedule_id'].value = emp.schedule_id || '';

    // Province/City/Barangay restore (ph-location.js will handle cascading)
    if (emp.current_province) {
      document.getElementById('current_province').value = emp.current_province;
      document.getElementById('current_city').value = emp.current_city || '';
      document.getElementById('current_barangay').value = emp.current_barangay || '';
      document.getElementById('current_postal_code').value = emp.current_postal_code || '';
    }
  });
});
</script>
@endpush
