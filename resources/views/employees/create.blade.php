<!-- Profile Section -->
<div class="card mb-3">
    <div class="card-header">Profile</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label>Profile Photo</label>
                <input type="file" class="form-control" name="profile_picture" accept=".jpg, .jpeg, .png">
            </div>
            <div class="col-md-3">
                <label>First Name</label>
                <input type="text" class="form-control" name="first_name" required>
            </div>
            <div class="col-md-3">
                <label>Middle Name</label>
                <input type="text" class="form-control" name="middle_name">
            </div>
            <div class="col-md-3">
                <label>Last Name</label>
                <input type="text" class="form-control" name="last_name" required>
            </div>
        </div>
    </div>
</div>

<!-- Address Section -->
<div class="card mb-3">
    <div class="card-header">Address Details</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <label>Current Address</label>
                <input type="text" class="form-control" name="current_address" required>
            </div>
            <div class="col-md-6">
                <label>Permanent Address</label>
                <input type="text" class="form-control" name="permanent_address">
            </div>
        </div>
    </div>
</div>

<!-- Family Section -->
<div class="card mb-3">
    <div class="card-header">Family Details</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <label>Father's Name</label>
                <input type="text" class="form-control" name="father_name">
            </div>
            <div class="col-md-6">
                <label>Mother's Name</label>
                <input type="text" class="form-control" name="mother_name">
            </div>
        </div>
    </div>
</div>

<!-- Experience Section -->
<div class="card mb-3">
    <div class="card-header">Experience</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Previous Company</label>
                <input type="text" class="form-control" name="previous_company">
            </div>
            <div class="col-md-4">
                <label>Job Title</label>
                <input type="text" class="form-control" name="job_title">
            </div>
            <div class="col-md-4">
                <label>Years of Experience</label>
                <input type="number" class="form-control" name="years_experience">
            </div>
        </div>
    </div>
</div>

<!-- Contact Info -->
<div class="card mb-3">
    <div class="card-header">Contact Info</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <label>Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
        </div>
    </div>
</div>

<!-- Other Information -->
<div class="card mb-3">
    <div class="card-header">Other Information</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <label>Gender</label>
                <select class="form-control" name="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Date of Birth</label>
                <input type="date" class="form-control" name="dob">
            </div>
            <div class="col-md-4">
                <label>Nationality</label>
                <input type="text" class="form-control" name="nationality">
            </div>
        </div>
    </div>
</div>

<!-- Work Details -->
<div class="card mb-3">
    <div class="card-header">Work Details</div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <label>Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">Select Department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label>Designation</label>
                <select name="designation_id" class="form-control" required>
                    <option value="">Select Designation</option>
                    @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Fingerprint Placeholder -->
<div class="mt-3">
    <label>Fingerprint</label>
    <input type="text" class="form-control" disabled value="Pending fingerprint scan">
</div>
