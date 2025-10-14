{{-- resources/views/employees/show.blade.php --}}
<div class="modal fade" id="viewEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-white">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i class="bi bi-person-lines-fill"></i>
          <span class="ve-employee_code">—</span> — <span class="ve-full_name">—</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">

          {{-- LEFT COLUMN: Info --}}
          <div class="col-lg-8">

            {{-- BASIC INFO --}}
            <table class="table table-bordered mb-4">
              <thead class="table-light"><tr><th colspan="2" class="text-center">Basic Info</th></tr></thead>
              <tbody>
                <tr><th style="width:35%">Employee Code</th><td class="ve-employee_code">—</td></tr>
                <tr><th>Full Name</th>        <td class="ve-full_name">—</td></tr>
                <tr><th>Email</th>            <td class="ve-email">—</td></tr>
                <tr><th>Role</th>             <td class="ve-role">—</td></tr>
                <tr><th>Status</th>           <td class="ve-employment_status">—</td></tr>
                <tr><th>Department</th>       <td class="ve-department">—</td></tr>
                <tr><th>Designation</th>      <td class="ve-designation">—</td></tr>
                <tr><th>Schedule</th>         <td class="ve-schedule">—</td></tr>
                <tr><th>Employment Type</th>  <td class="ve-employment_type">—</td></tr>
                <tr><th>Start–End</th>        <td class="ve-start_end">—</td></tr>
              </tbody>
            </table>

            {{-- PERSONAL --}}
            <table class="table table-bordered mb-4">
              <thead class="table-light"><tr><th colspan="2" class="text-center">Personal</th></tr></thead>
              <tbody>
                <tr><th style="width:35%">Gender</th><td class="ve-gender">—</td></tr>
                <tr><th>DOB</th>              <td class="ve-dob">—</td></tr>
                <tr><th>Current Address</th>  <td class="ve-current_address">—</td></tr>
                <tr><th>Permanent Address</th><td class="ve-permanent_address">—</td></tr>
              </tbody>
            </table>

            {{-- EDUCATIONAL BACKGROUND --}}
            <table class="table table-striped mb-4">
              <thead class="table-light"><tr><th colspan="2" class="text-center">Educational Background</th></tr></thead>
              <tbody>
                <tr><th style="width:35%">Elementary</th><td class="ve-elementary_school">—</td></tr>
                <tr><th>Year Graduated</th>  <td class="ve-elementary_year_graduated">—</td></tr>
                <tr><th>High School</th>     <td class="ve-high_school">—</td></tr>
                <tr><th>Year Graduated</th>  <td class="ve-high_school_year_graduated">—</td></tr>
                <tr><th>College</th>         <td class="ve-college">—</td></tr>
                <tr><th>Year Graduated</th>  <td class="ve-college_year_graduated">—</td></tr>
                <tr><th>Degree Received</th> <td class="ve-degree_received">—</td></tr>
                <tr><th>Special Skills</th>  <td class="ve-special_skills">—</td></tr>
              </tbody>
            </table>

            {{-- EMPLOYMENT RECORD --}}
            <table class="table table-bordered mb-4">
              <thead class="table-light"><tr><th colspan="4" class="text-center">Employment Record</th></tr></thead>
              <tbody>
                <tr><th style="width:20%">Company</th><td class="ve-emp1_company">—</td><th style="width:20%">Position</th><td class="ve-emp1_position">—</td></tr>
                <tr><th>From</th><td class="ve-emp1_from">—</td><th>To</th><td class="ve-emp1_to">—</td></tr>
                <tr><th>Company</th><td class="ve-emp2_company">—</td><th>Position</th><td class="ve-emp2_position">—</td></tr>
                <tr><th>From</th><td class="ve-emp2_from">—</td><th>To</th><td class="ve-emp2_to">—</td></tr>
              </tbody>
            </table>

            {{-- CERTIFICATIONS & IDs --}}
            <table class="table table-bordered mb-0">
              <thead class="table-light"><tr><th colspan="2" class="text-center">Certifications & IDs</th></tr></thead>
              <tbody>
                <tr><th style="width:35%">Res. Cert. No.</th><td class="ve-res_cert_no">—</td></tr>
                <tr><th>Issued At</th><td class="ve-res_cert_issued_at">—</td></tr>
                <tr><th>Issued On</th><td class="ve-res_cert_issued_on">—</td></tr>
                <tr><th>SSS No.</th><td class="ve-sss_no">—</td></tr>
                <tr><th>TIN No.</th><td class="ve-tin_no">—</td></tr>
                <tr><th>NBI No.</th><td class="ve-nbi_no">—</td></tr>
                <tr><th>Passport No.</th><td class="ve-passport_no">—</td></tr>
              </tbody>
            </table>
          </div>

          {{-- RIGHT COLUMN: Docs + Headshot --}}
          <div class="col-lg-4">
            <div class="card h-100">
              <div class="card-header">Documents</div>
              <div class="card-body">

                {{-- Headshot --}}
                <div class="mb-3">
                  <label class="form-label fw-semibold d-block">Profile Picture</label>
                  <div class="headshot border rounded bg-light overflow-hidden mb-2" style="width:160px;height:160px;">
                    <img id="ve-profile-img" class="w-100 h-100" style="object-fit:cover;display:none;">
                    <div id="ve-profile-empty" class="d-flex align-items-center justify-content-center text-muted">No image</div>
                  </div>
                  <a id="ve-profile-link" href="#" target="_blank" class="small d-none"><i class="bi bi-box-arrow-up-right"></i> Open full image</a>
                </div>

                {{-- Resume --}}
                <div class="mb-3">
                  <label class="form-label fw-semibold">Resume</label>
                  <div class="border rounded bg-light p-2">
                    <div id="ve-resume-preview" class="d-none">
                      <iframe id="ve-resume-pdf" class="d-none" style="width:100%;height:260px;border:0;"></iframe>
                      <img id="ve-resume-img" class="img-fluid d-none rounded">
                    </div>
                    <div id="ve-resume-none" class="text-muted small">No file</div>
                  </div>
                  <div class="mt-2 small">
                    <a id="ve-resume-link" href="#" target="_blank" class="d-none"><i class="bi bi-download"></i> Download / Open</a>
                  </div>
                </div>

                {{-- MDRs --}}
                <div class="mb-3">
                  <label class="form-label fw-semibold">MDRs</label>
                  <ul class="list-unstyled small mb-0">
                    <li><a id="ve-mdr-ph-link" class="d-none" target="_blank">PhilHealth</a><span id="ve-mdr-ph-none" class="text-muted">—</span></li>
                    <li><a id="ve-mdr-sss-link" class="d-none" target="_blank">SSS</a><span id="ve-mdr-sss-none" class="text-muted">—</span></li>
                    <li><a id="ve-mdr-pagibig-link" class="d-none" target="_blank">Pag-IBIG</a><span id="ve-mdr-pagibig-none" class="text-muted">—</span></li>
                  </ul>
                </div>

                {{-- Medical Docs --}}
                <div>
                  <label class="form-label fw-semibold">Medical Documents</label>
                  <ul id="ve-medical-list" class="list-unstyled small mb-0">
                    <li id="ve-medical-none" class="text-muted">—</li>
                  </ul>
                </div>

              </div>
            </div>
          </div>

        </div>
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
  const fileUrl = (p) => p ? `/storage/${p}` : '';
  const setText = (sel,v) => { const el=modalEl.querySelector(sel); if(el) el.textContent=(v||'').toString().trim()||'—'; };
  const setLink = (link,none,path,label) => {
    const a=modalEl.querySelector(link),n=none?modalEl.querySelector(none):null;
    const href=path?fileUrl(path):''; 
    if(href){a.href=href;a.textContent=label||a.textContent||'Open';a.classList.remove('d-none');if(n)n.classList.add('d-none');}
    else{a.classList.add('d-none');if(n)n.classList.remove('d-none');}
  };
  const isPdf = (u)=>typeof u==='string'&&u.toLowerCase().includes('.pdf');

  modalEl.addEventListener('show.bs.modal',(evt)=>{
    const btn=evt.relatedTarget; let emp={}; 
    try{emp=JSON.parse(btn?.getAttribute('data-employee')||'{}');}catch(_){}

    // basics
    setText('.ve-employee_code', emp.employee_code);
    setText('.ve-full_name', emp.name || [emp.first_name, emp.last_name].filter(Boolean).join(' '));
    setText('.ve-email', emp.email);
    setText('.ve-role', emp.role);
    setText('.ve-employment_status', emp.employment_status);
    setText('.ve-department', emp.department);
    setText('.ve-designation', emp.designation);
    setText('.ve-schedule', emp.schedule_in&&emp.schedule_out?`${emp.schedule_in}–${emp.schedule_out}`:'');
    setText('.ve-employment_type', emp.employment_type);
    setText('.ve-start_end', [emp.employment_start_date,emp.employment_end_date].filter(Boolean).join(' – '));

    // personal
    setText('.ve-gender', emp.gender);
    setText('.ve-dob', emp.dob);
    const curr=[emp.current_street_address,emp.current_city,emp.current_province,emp.current_postal_code].filter(Boolean).join(', ');
    setText('.ve-current_address', curr);
    setText('.ve-permanent_address', emp.permanent_address);

    // education
    setText('.ve-elementary_school', emp.elementary_school);
    setText('.ve-elementary_year_graduated', emp.elementary_year_graduated);
    setText('.ve-high_school', emp.high_school);
    setText('.ve-high_school_year_graduated', emp.high_school_year_graduated);
    setText('.ve-college', emp.college);
    setText('.ve-college_year_graduated', emp.college_year_graduated);
    setText('.ve-degree_received', emp.degree_received);
    setText('.ve-special_skills', emp.special_skills);

    // employment
    setText('.ve-emp1_company', emp.emp1_company);
    setText('.ve-emp1_position', emp.emp1_position);
    setText('.ve-emp1_from', emp.emp1_from);
    setText('.ve-emp1_to', emp.emp1_to);
    setText('.ve-emp2_company', emp.emp2_company);
    setText('.ve-emp2_position', emp.emp2_position);
    setText('.ve-emp2_from', emp.emp2_from);
    setText('.ve-emp2_to', emp.emp2_to);

    // headshot
    const profImg=modalEl.querySelector('#ve-profile-img'),profNone=modalEl.querySelector('#ve-profile-empty'),profLink=modalEl.querySelector('#ve-profile-link');
    if(emp.profile_picture){
      const url=fileUrl(emp.profile_picture);
      profImg.src=url;
      profImg.style.display='block';
      profNone.classList.add('d-none');
      profLink.href=url;
      profLink.classList.remove('d-none');
    } else {
      profImg.removeAttribute('src');
      profImg.style.display='none';
      profNone.classList.remove('d-none');
      profLink.classList.add('d-none');
    }

    // resume
    const resumePath=emp.resume_file||'';
    const box=modalEl.querySelector('#ve-resume-preview'),none=modalEl.querySelector('#ve-resume-none'),
          pdf=modalEl.querySelector('#ve-resume-pdf'),img=modalEl.querySelector('#ve-resume-img'),
          link=modalEl.querySelector('#ve-resume-link');
    if(resumePath){
      const url=fileUrl(resumePath);
      none.classList.add('d-none'); box.classList.remove('d-none');
      link.href=url; link.classList.remove('d-none');
      if(isPdf(url)){pdf.src=url; pdf.classList.remove('d-none'); img.classList.add('d-none');}
      else{img.src=url; img.classList.remove('d-none'); pdf.classList.add('d-none');}
    } else {
      link.classList.add('d-none'); box.classList.add('d-none'); none.classList.remove('d-none');
      pdf.src=''; img.removeAttribute('src');
    }

    // mdrs
    setLink('#ve-mdr-ph-link','#ve-mdr-ph-none',emp.mdr_philhealth_file,'PhilHealth');
    setLink('#ve-mdr-sss-link','#ve-mdr-sss-none',emp.mdr_sss_file,'SSS');
    setLink('#ve-mdr-pagibig-link','#ve-mdr-pagibig-none',emp.mdr_pagibig_file,'Pag-IBIG');

    // medical docs
    const medList=modalEl.querySelector('#ve-medical-list'),medNone=modalEl.querySelector('#ve-medical-none');
    medList.querySelectorAll('li.med-item').forEach(li=>li.remove());
    const docs=Array.isArray(emp.medical_documents)?emp.medical_documents:[];
    if(docs.length){
      medNone.classList.add('d-none');
      docs.forEach((d,i)=>{
        const li=document.createElement('li'); li.className='med-item mb-1';
        const path=typeof d==='string'?d:(d.path||d.url||'');
        if(path){
          const a=document.createElement('a'); a.href=fileUrl(path); a.target='_blank'; a.rel='noopener'; a.textContent=`Document ${i+1}`;
          li.appendChild(a);
        } else {
          li.textContent=`Document ${i+1}`;
        }
        medList.appendChild(li);
      });
    } else {
      medNone.classList.remove('d-none');
    }
  });
});
</script>
@endpush
