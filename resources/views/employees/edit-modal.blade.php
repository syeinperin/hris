{{-- resources/views/employees/edit-modal.blade.php --}}
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="editEmployeeForm" action="#" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="modal-header">
          <h5 class="modal-title" id="editEmployeeModalLabel">
            <i class="bi bi-pencil-square me-2"></i>
            Edit <span class="js-emp-code">Employee</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body" style="max-height:calc(100vh - 200px);overflow-y:auto;">

          @if ($errors->any())
            <div class="alert alert-danger mb-4">
              <strong>Please fix the errors below:</strong>
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- Tabs --}}
          <ul class="nav nav-tabs mb-4" id="empEditTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-edit-account" type="button" role="tab">Account</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-personal" type="button" role="tab">Personal</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-work" type="button" role="tab">Work</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-benefits" type="button" role="tab">Benefits</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-education" type="button" role="tab">Education</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-emphistory" type="button" role="tab">Employment History</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-characters" type="button" role="tab">Character References</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-edit-certificates" type="button" role="tab">Certificates & Docs</button>
            </li>
          </ul>

          <div class="tab-content" id="empEditTabContent">

            {{-- ACCOUNT --}}
            <div class="tab-pane fade show active" id="tab-edit-account" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="email" name="email" class="form-control" placeholder="Email *" value="" required>
                  <label>Email *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select name="role" class="form-select" required>
                    <option value="" disabled selected>-- Select Role --</option>
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
                  <input type="password" name="password" class="form-control" placeholder="New Password">
                  <label>New Password</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm">
                  <label>Confirm</label>
                </div>
              </div>
            </div>

            {{-- PERSONAL --}}
            <div class="tab-pane fade" id="tab-edit-personal" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="text" name="first_name" class="form-control" placeholder="First Name *" value="" required>
                  <label>First Name *</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="middle_name" class="form-control" placeholder="Middle Name" value="">
                  <label>Middle Name</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="last_name" class="form-control" placeholder="Last Name *" value="" required>
                  <label>Last Name *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select name="gender" class="form-select" required>
                    <option value="" disabled selected>-- Select Gender --</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                  <label>Gender *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <input type="date" name="dob" class="form-control" placeholder="Date of Birth *" value="" required>
                  <label>Date of Birth *</label>
                </div>

                <div class="col-md-4">
                  <label>Profile Picture</label>
                  <input type="file" name="profile_picture" class="form-control">
                </div>

                <div class="col-12"><strong>Current Address *</strong></div>
                <div class="col-12 form-floating">
                  <input type="text" name="current_street_address" class="form-control" placeholder="Street Address *" value="" required>
                  <label>Street Address *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select id="current_province" name="current_province" class="form-select" required>
                    <option value="" disabled selected>Select Province *</option>
                    @foreach($philippineProvinces as $prov)
                      <option value="{{ $prov }}">{{ $prov }}</option>
                    @endforeach
                  </select>
                  <label>Province *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select id="current_city" name="current_city" class="form-select" data-value="" required>
                    <option value="" disabled selected>Select City *</option>
                  </select>
                  <label>City *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <input type="text" id="current_postal_code" name="current_postal_code" class="form-control" placeholder="ZIP Code" value="" readonly>
                  <label>ZIP Code</label>
                </div>

                <div class="col-md-6 form-floating">
                  <input type="text" name="permanent_address" class="form-control" placeholder="Permanent Address" value="">
                  <label>Permanent Address</label>
                </div>

                <div class="col-md-3 form-floating">
                  <input type="text" name="birth_place" class="form-control" placeholder="Birth place" value="">
                  <label>Birth Place</label>
                </div>

                <div class="col-md-3 form-floating">
                  <select name="civil_status" class="form-select">
                    <option value="">—</option>
                    @foreach(['single','married','widowed','separated','other'] as $cs)
                      <option value="{{ $cs }}">{{ ucfirst($cs) }}</option>
                    @endforeach
                  </select>
                  <label>Civil Status</label>
                </div>
              </div>
            </div>

            {{-- WORK --}}
            <div class="tab-pane fade" id="tab-edit-work" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <select name="department_id" class="form-select" required>
                    <option value="" disabled selected>-- Select Department --</option>
                    @foreach($departments as $id => $name)
                      <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                  </select>
                  <label>Department *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select name="designation_id" class="form-select" required>
                    <option value="" disabled selected>-- Select Designation --</option>
                    @foreach($designations as $id => $name)
                      <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                  </select>
                  <label>Designation *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select name="schedule_id" class="form-select">
                    <option value="">Schedule (optional)</option>
                    @foreach($schedules as $id => $name)
                      <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                  </select>
                  <label>Schedule</label>
                </div>

                <div class="col-md-4 form-floating">
                  <select name="employment_type" class="form-select" required>
                    <option value="" disabled selected>-- Select Employment Type --</option>
                    @foreach($employmentTypes as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  <label>Employment Type *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <input type="date" name="employment_start_date" class="form-control" placeholder="Start Date" value="">
                  <label>Start Date</label>
                </div>

                <div class="col-md-4 form-floating">
                  <input type="date" name="employment_end_date" class="form-control" placeholder="End Date *" value="" required>
                  <label>End Date *</label>
                </div>

                <div class="col-md-4 form-floating">
                  <input type="text" name="fingerprint_id" class="form-control" placeholder="Fingerprint ID" value="">
                  <label>Fingerprint ID</label>
                </div>
              </div>
            </div>

            {{-- BENEFITS --}}
            <div class="tab-pane fade" id="tab-edit-benefits" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="text" name="gsis_id_no" class="form-control" placeholder="GSIS ID No." value="">
                  <label>GSIS ID No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="pagibig_id_no" class="form-control" placeholder="PAGIBIG ID No." value="">
                  <label>PAGIBIG ID No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="philhealth_tin_id_no" class="form-control" placeholder="PHILHEALTH TIN ID No." value="">
                  <label>PHILHEALTH TIN ID No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="sss_no" class="form-control" placeholder="SSS No." value="">
                  <label>SSS No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="tin_no" class="form-control" placeholder="TIN No." value="">
                  <label>TIN No.</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="agency_employee_no" class="form-control" placeholder="Agency Employee No." value="">
                  <label>Agency Employee No.</label>
                </div>
              </div>
            </div>

            {{-- EDUCATION --}}
            <div class="tab-pane fade" id="tab-edit-education" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-6 form-floating">
                  <input type="text" name="elementary_school" class="form-control" placeholder="Elementary" value="">
                  <label>Elementary</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="elementary_year_graduated" class="form-control" placeholder="Year Graduated" value="">
                  <label>Year Graduated</label>
                </div>

                <div class="col-md-6 form-floating">
                  <input type="text" name="high_school" class="form-control" placeholder="High School" value="">
                  <label>High School</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="high_school_year_graduated" class="form-control" placeholder="Year Graduated" value="">
                  <label>Year Graduated</label>
                </div>

                <div class="col-md-6 form-floating">
                  <input type="text" name="college" class="form-control" placeholder="College" value="">
                  <label>College</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="college_year_graduated" class="form-control" placeholder="Year Graduated" value="">
                  <label>Year Graduated</label>
                </div>

                <div class="col-md-6 form-floating">
                  <input type="text" name="degree_received" class="form-control" placeholder="Degree Received" value="">
                  <label>Degree Received</label>
                </div>
                <div class="col-md-6 form-floating">
                  <input type="text" name="special_skills" class="form-control" placeholder="Special Skills" value="">
                  <label>Special Skills</label>
                </div>
              </div>
            </div>

            {{-- EMPLOYMENT HISTORY --}}
            <div class="tab-pane fade" id="tab-edit-emphistory" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-4 form-floating">
                  <input type="text" name="emp1_company" class="form-control" placeholder="Company" value="">
                  <label>Company (1)</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="emp1_position" class="form-control" placeholder="Position" value="">
                  <label>Position (1)</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="date" name="emp1_from" class="form-control" placeholder="From" value="">
                  <label>From (1)</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="date" name="emp1_to" class="form-control" placeholder="To" value="">
                  <label>To (1)</label>
                </div>

                <div class="col-md-4 form-floating">
                  <input type="text" name="emp2_company" class="form-control" placeholder="Company" value="">
                  <label>Company (2)</label>
                </div>
                <div class="col-md-4 form-floating">
                  <input type="text" name="emp2_position" class="form-control" placeholder="Position" value="">
                  <label>Position (2)</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="date" name="emp2_from" class="form-control" placeholder="From" value="">
                  <label>From (2)</label>
                </div>
                <div class="col-md-2 form-floating">
                  <input type="date" name="emp2_to" class="form-control" placeholder="To" value="">
                  <label>To (2)</label>
                </div>
              </div>
            </div>

            {{-- CERTIFICATES & DOCS --}}
            <div class="tab-pane fade" id="tab-edit-certificates" role="tabpanel">
              <div class="row g-3">
                <div class="col-md-3 form-floating">
                  <input type="text" name="res_cert_no" class="form-control" placeholder="Res. Cert. No." value="">
                  <label>Res. Cert. No.</label>
                </div>
                <div class="col-md-3 form-floating">
                  <input type="text" name="res_cert_issued_at" class="form-control" placeholder="Issued At" value="">
                  <label>Issued At</label>
                </div>
                <div class="col-md-3 form-floating">
                  <input type="date" name="res_cert_issued_on" class="form-control" placeholder="Issued On" value="">
                  <label>Issued On</label>
                </div>
                <div class="col-md-3 form-floating">
                  <input type="text" name="nbi_no" class="form-control" placeholder="NBI No." value="">
                  <label>NBI No.</label>
                </div>
                <div class="col-md-3 form-floating">
                  <input type="text" name="passport_no" class="form-control" placeholder="Passport No." value="">
                  <label>Passport No.</label>
                </div>
              </div>
            </div>

          </div> {{-- /tab-content --}}
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save2 me-1"></i> Update
          </button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
/**
 * Minimal PH location map fallback (use your ph-location.js if present).
 * If window.PH_LOCATIONS exists (from resources/js/ph-location.js), that is used.
 */
(function () {
  const MAP = window.PH_LOCATIONS || {
    'Cavite': {
      'Bacoor': {zip:'4102'}, 'Cavite City': {zip:'4100'}, 'Imus': {zip:'4103'},
      'Dasmariñas': {zip:'4114'}, 'General Trias': {zip:'4107'}
    },
    'Laguna': {
      'Calamba': {zip:'4027'}, 'Biñan': {zip:'4024'}, 'Santa Rosa': {zip:'4026'},
      'San Pablo': {zip:'4000'}, 'Los Baños': {zip:'4030'}, 'Alaminos': {zip:'4001'},
      'Bay': {zip:'4033'}, 'Cabuyao': {zip:'4025'}, 'Cavinti': {zip:'4013'},
      'Famy': {zip:'4021'}, 'Kalayaan': {zip:'4015'}, 'Liliw': {zip:'4004'},
      'Luisiana': {zip:'4032'}, 'Lumban': {zip:'4014'}, 'Mabitac': {zip:'4020'}
    }
  };

  function populateCities(province, cityEl) {
    cityEl.innerHTML = '<option value="" disabled selected>Select City *</option>';
    const cities = MAP[province] ? Object.keys(MAP[province]) : [];
    cities.forEach(c => cityEl.add(new Option(c, c)));
    cityEl.disabled = cities.length === 0;
  }

  function updateZip(province, city, zipEl) {
    if (!zipEl) return;
    const z = (MAP[province] && MAP[province][city] && MAP[province][city].zip) || '';
    zipEl.value = z;
  }

  function bindPhControls(form) {
    const provEl = form.querySelector('#current_province');
    const cityEl = form.querySelector('#current_city');
    const zipEl  = form.querySelector('#current_postal_code');
    if (!provEl || !cityEl) return;

    // Debounce duplicate bindings
    provEl._phBound && provEl.removeEventListener('change', provEl._phBound);
    cityEl._phBound && cityEl.removeEventListener('change', cityEl._phBound);

    const onProvChange = function () {
      populateCities(provEl.value, cityEl);
      // restore selected city if we carried it in data-value
      if (cityEl.dataset.value) cityEl.value = cityEl.dataset.value;
      updateZip(provEl.value, cityEl.value, zipEl);
    };
    const onCityChange = function () {
      updateZip(provEl.value, cityEl.value, zipEl);
    };

    provEl.addEventListener('change', onProvChange);
    cityEl.addEventListener('change', onCityChange);
    provEl._phBound = onProvChange;
    cityEl._phBound = onCityChange;

    // Initial populate
    onProvChange();
  }

  // ===== Modal behavior =====
  const modalEl = document.getElementById('editEmployeeModal');
  if (!modalEl) return;

  modalEl.addEventListener('show.bs.modal', function (evt) {
    const btn  = evt.relatedTarget;
    if (!btn) return;

    const form = modalEl.querySelector('#editEmployeeForm');
    const titleCodeEl = modalEl.querySelector('.js-emp-code');

    // Set form action for PUT
    form.action = btn.dataset.action || '#';

    // Parse payload
    let data = {};
    try { data = JSON.parse(btn.dataset.employee || '{}'); } catch (_) {}

    titleCodeEl.textContent = data.employee_code || 'Employee';

    // Helper
    const setVal = (name, value) => {
      const el = form.querySelector(`[name="${name}"]`);
      if (!el) return;

      if (el.tagName === 'SELECT') {
        // ensure option exists (defensive)
        if (value != null && value !== '' && ![...el.options].some(o => o.value == value)) {
          const opt = document.createElement('option');
          opt.value = value;
          opt.textContent = value;
          el.appendChild(opt);
        }
        el.value = value ?? '';
      } else if (el.type !== 'file') {
        el.value = value ?? '';
      }
    };

    // Account
    setVal('email', data.email);
    setVal('role', data.role || 'employee'); // sensible default
    setVal('status', (data.status || data.employment_status || 'active').toString().toLowerCase());

    // Personal
    setVal('first_name', data.first_name);
    setVal('middle_name', data.middle_name);
    setVal('last_name', data.last_name);
    setVal('gender', data.gender);
    setVal('dob', data.dob);
    setVal('current_street_address', data.current_street_address);

    // Province first so cities can populate
    setVal('current_province', data.current_province);

    // Park desired city in dataset so binder can apply it
    const cityEl = form.querySelector('#current_city');
    const zipEl  = form.querySelector('#current_postal_code');
    if (cityEl) cityEl.dataset.value = data.current_city || '';

    setVal('permanent_address', data.permanent_address);
    setVal('birth_place', data.birth_place);
    setVal('civil_status', data.civil_status);

    // Work
    setVal('department_id', data.department_id);
    setVal('designation_id', data.designation_id);
    setVal('schedule_id', data.schedule_id);
    setVal('employment_type', data.employment_type);
    setVal('employment_start_date', data.employment_start_date);
    setVal('employment_end_date', data.employment_end_date);
    setVal('fingerprint_id', data.fingerprint_id);

    // Benefits
    setVal('gsis_id_no', data.gsis_id_no);
    setVal('pagibig_id_no', data.pagibig_id_no);
    setVal('philhealth_tin_id_no', data.philhealth_tin_id_no);
    setVal('sss_no', data.sss_no);
    setVal('tin_no', data.tin_no);
    setVal('agency_employee_no', data.agency_employee_no);

    // Education
    setVal('elementary_school', data.elementary_school);
    setVal('elementary_year_graduated', data.elementary_year_graduated);
    setVal('high_school', data.high_school);
    setVal('high_school_year_graduated', data.high_school_year_graduated);
    setVal('college', data.college);
    setVal('college_year_graduated', data.college_year_graduated);
    setVal('degree_received', data.degree_received);
    setVal('special_skills', data.special_skills);

    // Employment history
    setVal('emp1_company', data.emp1_company);
    setVal('emp1_position', data.emp1_position);
    setVal('emp1_from', data.emp1_from);
    setVal('emp1_to', data.emp1_to);
    setVal('emp2_company', data.emp2_company);
    setVal('emp2_position', data.emp2_position);
    setVal('emp2_from', data.emp2_from);
    setVal('emp2_to', data.emp2_to);

    // Certificates
    setVal('res_cert_no', data.res_cert_no);
    setVal('res_cert_issued_at', data.res_cert_issued_at);
    setVal('res_cert_issued_on', data.res_cert_issued_on);
    setVal('nbi_no', data.nbi_no);
    setVal('passport_no', data.passport_no);

    // Bind PH locations (keeps City enabled in edit)
    bindPhControls(form);

    // If we already know city, update ZIP as well (after binding)
    if (zipEl && data.current_city) {
      zipEl.value = (MAP[data.current_province] && MAP[data.current_province][data.current_city]?.zip) || (data.current_postal_code || '');
    }
  });

  // If there were validation errors, auto-show the modal so users see them immediately
  @if ($errors->any())
    document.addEventListener('DOMContentLoaded', function () {
      const m = document.getElementById('editEmployeeModal');
      if (m) new bootstrap.Modal(m).show();
    });
  @endif
})();
</script>
@endpush
