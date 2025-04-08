@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">User Management</h2>

    <!-- Filter/Search Form (GET) -->
    <form action="{{ route('users.index') }}" method="GET" class="row mb-3">
        <div class="col-md-3">
            <label for="search" class="form-label">Search (Name or Email)</label>
            <input type="text" name="search" id="search" class="form-control"
                   value="{{ request('search') }}" placeholder="Search users">
        </div>
        <div class="col-md-2">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-select">
                <option value="">All Roles</option>
                <option value="admin"      {{ request('role')=='admin'      ? 'selected' : '' }}>Admin</option>
                <option value="hr"         {{ request('role')=='hr'         ? 'selected' : '' }}>HR</option>
                <option value="employee"   {{ request('role')=='employee'   ? 'selected' : '' }}>Employee</option>
                <option value="supervisor" {{ request('role')=='supervisor' ? 'selected' : '' }}>Supervisor</option>
                <option value="timekeeper" {{ request('role')=='timekeeper' ? 'selected' : '' }}>Timekeeper</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select">
                <option value="">All</option>
                <option value="active"   {{ request('status')=='active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-2">
            <label for="sort_by" class="form-label">Sort By</label>
            <select name="sort_by" id="sort_by" class="form-select">
                <option value="created_at" {{ request('sort_by')=='created_at' ? 'selected' : '' }}>Created Date</option>
                <option value="name"       {{ request('sort_by')=='name'       ? 'selected' : '' }}>Name</option>
            </select>
        </div>
        <div class="col-md-1">
            <label for="sort_order" class="form-label">Order</label>
            <select name="sort_order" id="sort_order" class="form-select">
                <option value="asc"  {{ request('sort_order')=='asc'  ? 'selected' : '' }}>ASC</option>
                <option value="desc" {{ request('sort_order')=='desc' ? 'selected' : '' }}>DESC</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    <!-- Bulk Actions Form (POST) -->
    <form action="{{ route('users.bulkAction') }}" method="POST">
        @csrf
        <div class="mb-2">
            <button type="submit" name="action" value="activate"   class="btn btn-success">Activate Selected</button>
            <button type="submit" name="action" value="deactivate" class="btn btn-warning">Deactivate Selected</button>
            <button type="submit" name="action" value="lock"       class="btn btn-danger">Lock Selected</button>
            <button type="submit" name="action" value="unlock"     class="btn btn-info">Unlock Selected</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select_all"></th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($users as $user)
                <tr>
                    <td><input type="checkbox" name="selected_ids[]" value="{{ $user->id }}"></td>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <!-- Role Dropdown (updates via AJAX) -->
                        <select class="form-select role-select" data-user-id="{{ $user->id }}">
                            <option value="admin"      {{ $user->role=='admin'      ? 'selected' : '' }}>Admin</option>
                            <option value="hr"         {{ $user->role=='hr'         ? 'selected' : '' }}>HR</option>
                            <option value="employee"   {{ $user->role=='employee'   ? 'selected' : '' }}>Employee</option>
                            <option value="supervisor" {{ $user->role=='supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="timekeeper" {{ $user->role=='timekeeper' ? 'selected' : '' }}>Timekeeper</option>
                        </select>
                    </td>
                    <td>
                        @if($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>{{ $user->last_login ? $user->last_login->format('Y-m-d H:i') : 'Never' }}</td>
                    <td>
                        <!-- Reset Password -->
                        <button type="button" class="btn btn-sm btn-outline-primary reset-password-btn" 
                                data-user-id="{{ $user->id }}">Reset Password
                        </button>
                        <!-- Change Password -->
                        <button type="button" class="btn btn-sm btn-outline-secondary change-password-btn" 
                                data-user-id="{{ $user->id }}" data-user-email="{{ $user->email }}">Change Password
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No users found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </form>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="changePasswordForm" action="" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
              <label for="new_password" class="form-label">New Password</label>
              <input type="password" name="new_password" id="new_password" class="form-control" required>
          </div>
          <div class="mb-3">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
          </div>
          <input type="hidden" name="user_id" id="cp_user_id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="resetPasswordForm" action="" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to reset the password for this user? A new password will be sent to their email.</p>
          <input type="hidden" name="user_id" id="rp_user_id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Reset Password</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // "Select All" checkbox
    const selectAll = document.getElementById('select_all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected_ids[]"]');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    }

    // Handle Role Change via AJAX
    document.querySelectorAll('.role-select').forEach(select => {
        select.addEventListener('change', function() {
            const userId = this.getAttribute('data-user-id');
            const newRole = this.value;
            fetch(`/users/${userId}/role`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ role: newRole })
            })
            .then(response => response.json())
            .then(data => console.log('Role updated', data))
            .catch(error => console.error('Error:', error));
        });
    });

    // Change Password Modal
    const changePasswordButtons = document.querySelectorAll('.change-password-btn');
    const changePasswordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    changePasswordButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            document.getElementById('changePasswordForm').setAttribute('action', `/users/${userId}/password`);
            document.getElementById('cp_user_id').value = userId;
            changePasswordModal.show();
        });
    });

    // Reset Password Modal
    const resetPasswordButtons = document.querySelectorAll('.reset-password-btn');
    const resetPasswordModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    resetPasswordButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            document.getElementById('resetPasswordForm').setAttribute('action', `/users/${userId}/reset-password`);
            document.getElementById('rp_user_id').value = userId;
            resetPasswordModal.show();
        });
    });
});
</script>
@endsection
