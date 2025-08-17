{{-- Shared "View Employee" modal. No $employees loop needed. --}}
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-white">
        <h5 class="modal-title">
          <i class="bi bi-person-lines-fill me-2"></i>
          <span class="ve-employee_code">—</span> —
          <span class="ve-full_name">—</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        {{-- BASIC INFO --}}
        <table class="table table-bordered mb-4">
          <thead class="table-light">
            <tr><th colspan="2" class="text-center">Basic Info</th></tr>
          </thead>
          <tbody>
            <tr><th style="width:35%">Employee Code</th><td class="ve-employee_code">—</td></tr>
            <tr><th>Full Name</th>        <td class="ve-full_name">—</td></tr>
            <tr><th>Email</th>            <td class="ve-email">—</td></tr>
            <tr><th>Role</th>             <td class="ve-role">—</td></tr>
            <tr><th>Status</th>           <td class="ve-employment_status">—</td></tr>
            <tr><th>Department</th>       <td class="ve-department">—</td></tr>
            <tr><th>Designation</th>      <td class="ve-designation">—</td></tr>
            <tr><th>Schedule (in–out)</th><td class="ve-schedule">—</td></tr>
            <tr><th>Employment Type</th>  <td class="ve-employment_type">—</td></tr>
            <tr><th>Start–End</th>        <td class="ve-start_end">—</td></tr>
          </tbody>
        </table>

        {{-- PERSONAL --}}
        <table class="table table-bordered mb-4">
          <thead class="table-light">
            <tr><th colspan="2" class="text-center">Personal</th></tr>
          </thead>
          <tbody>
            <tr><th style="width:35%">Gender</th><td class="ve-gender">—</td></tr>
            <tr><th>DOB</th>              <td class="ve-dob">—</td></tr>
            <tr><th>Current Address</th>  <td class="ve-current_address">—</td></tr>
            <tr><th>Permanent Address</th><td class="ve-permanent_address">—</td></tr>
          </tbody>
        </table>

        {{-- EDUCATIONAL BACKGROUND --}}
        <table class="table table-striped mb-4">
          <thead class="table-light">
            <tr><th colspan="2" class="text-center">Educational Background</th></tr>
          </thead>
          <tbody>
            <tr><th style="width:35%">Elementary</th>     <td class="ve-elementary_school">—</td></tr>
            <tr><th>Year Graduated</th>                   <td class="ve-elementary_year_graduated">—</td></tr>
            <tr><th>High School</th>                      <td class="ve-high_school">—</td></tr>
            <tr><th>Year Graduated</th>                   <td class="ve-high_school_year_graduated">—</td></tr>
            <tr><th>College</th>                          <td class="ve-college">—</td></tr>
            <tr><th>Year Graduated</th>                   <td class="ve-college_year_graduated">—</td></tr>
            <tr><th>Degree Received</th>                  <td class="ve-degree_received">—</td></tr>
            <tr><th>Special Skills</th>                   <td class="ve-special_skills">—</td></tr>
          </tbody>
        </table>

        {{-- EMPLOYMENT RECORD --}}
        <table class="table table-bordered mb-4">
          <thead class="table-light">
            <tr><th colspan="4" class="text-center">Employment Record</th></tr>
          </thead>
          <tbody>
            <tr>
              <th style="width:20%">Company</th> <td class="ve-emp1_company" style="width:30%">—</td>
              <th style="width:20%">Position</th> <td class="ve-emp1_position" style="width:30%">—</td>
            </tr>
            <tr>
              <th>From</th> <td class="ve-emp1_from">—</td>
              <th>To</th>   <td class="ve-emp1_to">—</td>
            </tr>
            <tr>
              <th>Company</th>  <td class="ve-emp2_company">—</td>
              <th>Position</th> <td class="ve-emp2_position">—</td>
            </tr>
            <tr>
              <th>From</th> <td class="ve-emp2_from">—</td>
              <th>To</th>   <td class="ve-emp2_to">—</td>
            </tr>
          </tbody>
        </table>

        {{-- CHARACTER REFERENCE --}}
        <table class="table table-striped mb-4">
          <thead class="table-light">
            <tr><th colspan="4" class="text-center">Character Reference</th></tr>
          </thead>
          <tbody>
            <tr>
              <th style="width:20%">Name</th>     <td class="ve-char1_name" style="width:30%">—</td>
              <th style="width:20%">Position</th>  <td class="ve-char1_position" style="width:30%">—</td>
            </tr>
            <tr>
              <th>Company</th> <td class="ve-char1_company">—</td>
              <th>Contact</th> <td class="ve-char1_contact">—</td>
            </tr>
            <tr>
              <th>Name</th>     <td class="ve-char2_name">—</td>
              <th>Position</th> <td class="ve-char2_position">—</td>
            </tr>
            <tr>
              <th>Company</th> <td class="ve-char2_company">—</td>
              <th>Contact</th> <td class="ve-char2_contact">—</td>
            </tr>
          </tbody>
        </table>

        {{-- CERTIFICATIONS & IDs --}}
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr><th colspan="2" class="text-center">Certifications & IDs</th></tr>
          </thead>
          <tbody>
            <tr><th style="width:35%">Res. Cert. No.</th>  <td class="ve-res_cert_no">—</td></tr>
            <tr><th>Issued At</th>                         <td class="ve-res_cert_issued_at">—</td></tr>
            <tr><th>Issued On</th>                         <td class="ve-res_cert_issued_on">—</td></tr>
            <tr><th>SSS No.</th>                           <td class="ve-sss_no">—</td></tr>
            <tr><th>TIN No.</th>                           <td class="ve-tin_no">—</td></tr>
            <tr><th>NBI No.</th>                           <td class="ve-nbi_no">—</td></tr>
            <tr><th>Passport No.</th>                      <td class="ve-passport_no">—</td></tr>
          </tbody>
        </table>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('viewEmployeeModal');

  function setText(selector, value) {
    const el = modalEl.querySelector(selector);
    if (el) el.textContent = (value ?? '').toString().trim() || '—';
  }

  modalEl.addEventListener('show.bs.modal', (evt) => {
    const trigger = evt.relatedTarget; // the button clicked
    let emp = {};
    try { emp = JSON.parse(trigger.getAttribute('data-employee') || '{}'); } catch(e){ emp = {}; }

    // Basic
    setText('.ve-employee_code', emp.employee_code);
    setText('.ve-full_name', emp.full_name);
    setText('.ve-email', emp.email);
    setText('.ve-role', emp.role);
    setText('.ve-employment_status', emp.employment_status);
    setText('.ve-department', emp.department);
    setText('.ve-designation', emp.designation);
    setText('.ve-employment_type', emp.employment_type);
    setText('.ve-schedule', (emp.schedule_in && emp.schedule_out) ? `${emp.schedule_in}–${emp.schedule_out}` : '');
    setText('.ve-start_end', [emp.employment_start_date, emp.employment_end_date].filter(Boolean).join(' – '));

    // Personal
    setText('.ve-gender', emp.gender);
    setText('.ve-dob', emp.dob);
    setText('.ve-current_address', [emp.current_street_address, emp.current_city, emp.current_province, emp.current_postal_code].filter(Boolean).join(', '));
    setText('.ve-permanent_address', emp.permanent_address);

    // Education
    setText('.ve-elementary_school', emp.elementary_school);
    setText('.ve-elementary_year_graduated', emp.elementary_year_graduated);
    setText('.ve-high_school', emp.high_school);
    setText('.ve-high_school_year_graduated', emp.high_school_year_graduated);
    setText('.ve-college', emp.college);
    setText('.ve-college_year_graduated', emp.college_year_graduated);
    setText('.ve-degree_received', emp.degree_received);
    setText('.ve-special_skills', emp.special_skills);

    // Employment record
    setText('.ve-emp1_company', emp.emp1_company);
    setText('.ve-emp1_position', emp.emp1_position);
    setText('.ve-emp1_from', emp.emp1_from);
    setText('.ve-emp1_to', emp.emp1_to);
    setText('.ve-emp2_company', emp.emp2_company);
    setText('.ve-emp2_position', emp.emp2_position);
    setText('.ve-emp2_from', emp.emp2_from);
    setText('.ve-emp2_to', emp.emp2_to);

    // Character reference
    setText('.ve-char1_name', emp.char1_name);
    setText('.ve-char1_position', emp.char1_position);
    setText('.ve-char1_company', emp.char1_company);
    setText('.ve-char1_contact', emp.char1_contact);
    setText('.ve-char2_name', emp.char2_name);
    setText('.ve-char2_position', emp.char2_position);
    setText('.ve-char2_company', emp.char2_company);
    setText('.ve-char2_contact', emp.char2_contact);

    // IDs
    setText('.ve-res_cert_no', emp.res_cert_no);
    setText('.ve-res_cert_issued_at', emp.res_cert_issued_at);
    setText('.ve-res_cert_issued_on', emp.res_cert_issued_on);
    setText('.ve-sss_no', emp.sss_no);
    setText('.ve-tin_no', emp.tin_no);
    setText('.ve-nbi_no', emp.nbi_no);
    setText('.ve-passport_no', emp.passport_no);
  });
});
</script>
@endpush
