@extends('layouts.app')

@section('page_title','My Profile')

@section('content')
<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>My Profile</h4>
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if($errors->has('too_soon'))
        <div class="alert alert-warning">{{ $errors->first('too_soon') }}</div>
      @endif

      <form action="{{ route('profile.update') }}"
            method="POST"
            enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Profile Picture --}}
        <div class="mb-4 text-center">
          <img
            src="{{ $user->profile_picture
                     ? asset('storage/'.$user->profile_picture)
                     : asset('images/default-avatar.png') }}"
            class="rounded-circle mb-2"
            width="100" height="100"
            style="object-fit:cover"
          ><br>
          <label class="form-label">Change Picture</label>
          <input type="file"
                 name="profile_picture"
                 class="form-control @error('profile_picture') is-invalid @enderror">
          @error('profile_picture')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Account & Personal --}}
        <div class="card mb-4">
          <div class="card-header">Account & Personal</div>
          <div class="card-body row g-3">
            <div class="col-md-6 form-floating">
              <input type="email"
                     name="email"
                     class="form-control @error('email') is-invalid @enderror"
                     placeholder="Email"
                     value="{{ old('email',$user->email) }}"
                     required>
              <label>Email</label>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 form-floating">
              <input type="password"
                     name="password"
                     class="form-control @error('password') is-invalid @enderror"
                     placeholder="New Password (optional)">
              <label>New Password</label>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 form-floating">
              <input type="password"
                     name="password_confirmation"
                     class="form-control"
                     placeholder="Confirm Password">
              <label>Confirm Password</label>
            </div>

            <div class="col-md-6 form-floating">
              <input type="text"
                     name="first_name"
                     class="form-control @error('first_name') is-invalid @enderror"
                     placeholder="First Name"
                     value="{{ old('first_name',$employee->first_name) }}"
                     required>
              <label>First Name</label>
              @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 form-floating">
              <input type="text"
                     name="middle_name"
                     class="form-control @error('middle_name') is-invalid @enderror"
                     placeholder="Middle Name"
                     value="{{ old('middle_name',$employee->middle_name) }}">
              <label>Middle Name</label>
              @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6 form-floating">
              <input type="text"
                     name="last_name"
                     class="form-control @error('last_name') is-invalid @enderror"
                     placeholder="Last Name"
                     value="{{ old('last_name',$employee->last_name) }}"
                     required>
              <label>Last Name</label>
              @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 form-floating">
              <select name="gender"
                      class="form-select @error('gender') is-invalid @enderror"
                      required>
                <option value="male"   {{ old('gender',$employee->gender)=='male'? 'selected':'' }}>Male</option>
                <option value="female" {{ old('gender',$employee->gender)=='female'? 'selected':'' }}>Female</option>
                <option value="other"  {{ old('gender',$employee->gender)=='other'? 'selected':'' }}>Other</option>
              </select>
              <label>Gender</label>
              @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 form-floating">
              <input type="date"
                     name="dob"
                     class="form-control @error('dob') is-invalid @enderror"
                     placeholder="Date of Birth"
                     value="{{ old('dob',$employee->dob?->format('Y-m-d')) }}"
                     required>
              <label>Date of Birth</label>
              @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 form-floating">
              <input type="text"
                     name="current_address"
                     class="form-control @error('current_address') is-invalid @enderror"
                     placeholder="Current Address"
                     value="{{ old('current_address',$employee->current_address) }}"
                     required>
              <label>Current Address</label>
              @error('current_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-12 form-floating">
              <input type="text"
                     name="permanent_address"
                     class="form-control @error('permanent_address') is-invalid @enderror"
                     placeholder="Permanent Address"
                     value="{{ old('permanent_address',$employee->permanent_address) }}">
              <label>Permanent Address</label>
              @error('permanent_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        @if($isHr)
        {{-- Work & Benefits (HR only) --}}
        <div class="card mb-4">
          <div class="card-header">Work & Benefits</div>
          <div class="card-body row g-3">
            <div class="col-md-4 form-floating">
              <select name="department_id"
                      class="form-select @error('department_id') is-invalid @enderror"
                      required>
                <option value="">Department…</option>
                @foreach($departments as $id => $n)
                  <option value="{{ $id }}"
                    {{ old('department_id',$employee->department_id)==$id?'selected':'' }}>
                    {{ $n }}
                  </option>
                @endforeach
              </select>
              <label>Department</label>
              @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 form-floating">
              <select name="designation_id"
                      class="form-select @error('designation_id') is-invalid @enderror"
                      required>
                <option value="">Designation…</option>
                @foreach($designations as $id => $n)
                  <option value="{{ $id }}"
                    {{ old('designation_id',$employee->designation_id)==$id?'selected':'' }}>
                    {{ $n }}
                  </option>
                @endforeach
              </select>
              <label>Designation</label>
              @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 form-floating">
              <select name="schedule_id"
                      class="form-select @error('schedule_id') is-invalid @enderror">
                <option value="">Schedule…</option>
                @foreach($schedules as $id => $n)
                  <option value="{{ $id }}"
                    {{ old('schedule_id',$employee->schedule_id)==$id?'selected':'' }}>
                    {{ $n }}
                  </option>
                @endforeach
              </select>
              <label>Schedule</label>
              @error('schedule_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 form-floating">
              <select name="employment_type"
                      class="form-select @error('employment_type') is-invalid @enderror"
                      required>
                @foreach($employmentTypes as $k => $l)
                  <option value="{{ $k }}"
                    {{ old('employment_type',$employee->employment_type)==$k?'selected':'' }}>
                    {{ $l }}
                  </option>
                @endforeach
              </select>
              <label>Employment Type</label>
              @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 form-floating">
              <input type="date"
                     name="employment_start_date"
                     class="form-control @error('employment_start_date') is-invalid @enderror"
                     value="{{ old('employment_start_date',$employee->employment_start_date?->format('Y-m-d')) }}"
                     required>
              <label>Start Date</label>
              @error('employment_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4 form-floating">
              <input type="date"
                     name="employment_end_date"
                     class="form-control @error('employment_end_date') is-invalid @enderror"
                     value="{{ old('employment_end_date',$employee->employment_end_date?->format('Y-m-d')) }}"
                     required>
              <label>End Date</label>
              @error('employment_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Benefits IDs --}}
            <div class="col-md-4 form-floating">
              <input type="text"
                     name="sss_no"
                     class="form-control @error('sss_no') is-invalid @enderror"
                     placeholder="SSS No."
                     value="{{ old('sss_no',$employee->sss_no) }}">
              <label>SSS No.</label>
              @error('sss_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 form-floating">
              <input type="text"
                     name="pagibig_id_no"
                     class="form-control @error('pagibig_id_no') is-invalid @enderror"
                     placeholder="PAGIBIG ID No."
                     value="{{ old('pagibig_id_no',$employee->pagibig_id_no) }}">
              <label>PAGIBIG ID No.</label>
              @error('pagibig_id_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 form-floating">
              <input type="text"
                     name="philhealth_tin_id_no"
                     class="form-control @error('philhealth_tin_id_no') is-invalid @enderror"
                     placeholder="PhilHealth TIN No."
                     value="{{ old('philhealth_tin_id_no',$employee->philhealth_tin_id_no) }}">
              <label>PhilHealth TIN No.</label>
              @error('philhealth_tin_id_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Work history & other --}}
            <div class="col-md-6 form-floating">
              <input type="text"
                     name="previous_company"
                     class="form-control @error('previous_company') is-invalid @enderror"
                     placeholder="Previous Company"
                     value="{{ old('previous_company',$employee->previous_company) }}">
              <label>Previous Company</label>
              @error('previous_company')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 form-floating">
              <input type="text"
                     name="job_title"
                     class="form-control @error('job_title') is-invalid @enderror"
                     placeholder="Job Title"
                     value="{{ old('job_title',$employee->job_title) }}">
              <label>Job Title</label>
              @error('job_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 form-floating">
              <input type="number" step="0.1"
                     name="years_experience"
                     class="form-control @error('years_experience') is-invalid @enderror"
                     placeholder="Years Experience"
                     value="{{ old('years_experience',$employee->years_experience) }}">
              <label>Years Experience</label>
              @error('years_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 form-floating">
              <input type="text"
                     name="nationality"
                     class="form-control @error('nationality') is-invalid @enderror"
                     placeholder="Nationality"
                     value="{{ old('nationality',$employee->nationality) }}">
              <label>Nationality</label>
              @error('nationality')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 form-floating">
              <input type="text"
                     name="fingerprint_id"
                     class="form-control @error('fingerprint_id') is-invalid @enderror"
                     placeholder="Fingerprint ID"
                     value="{{ old('fingerprint_id',$employee->fingerprint_id) }}">
              <label>Fingerprint ID</label>
              @error('fingerprint_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
        @endif

        <div class="text-end">
          <button class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
