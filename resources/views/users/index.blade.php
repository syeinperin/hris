@extends('layouts.app')

@section('page_title', 'User Management')

@section('content')
<div class="container">
  <h2 class="mb-4">User Management</h2>

  <x-search-bar
    :action="route('users.index')"
    placeholder="Search users…"
    :filters="[
      'role'   => [''=>'All Roles','admin'=>'Admin','hr'=>'HR','employee'=>'Employee','supervisor'=>'Supervisor','timekeeper'=>'Timekeeper'],
      'status' => [''=>'All','active'=>'Active','inactive'=>'Inactive'],
    ]"
    :sortFields="[
      'ID'         => 'id',
      'Name'       => 'name',
      'Email'      => 'email',
      'Created At' => 'created_at',
      'Last Login' => 'last_login',
    ]"
    showDateRange
  />

  <table class="table table-hover align-middle mt-3">
    <thead class="table-light">
      <tr>
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
      @forelse($users as $u)
        <tr>
          <td>{{ $u->id }}</td>
          <td>{{ $u->name }}</td>
          <td>{{ $u->email }}</td>
          <td>
            <select
              class="form-select role-select"
              data-update-url="{{ route('users.updateRole', $u) }}"
              data-current-role="{{ $u->role?->name }}"
            >
              @foreach(['admin','hr','employee','supervisor','timekeeper'] as $r)
                <option value="{{ $r }}"
                  {{ ($u->role?->name === $r) ? 'selected' : '' }}
                >{{ ucfirst($r) }}</option>
              @endforeach
            </select>
          </td>
          <td>
            @if($u->status === 'active' && $u->last_login)
              <span class="badge bg-success">Active</span>
            @else
              <span class="badge bg-secondary">Inactive</span>
            @endif
          </td>
          <td>
            @if($u->last_login)
              {{ \Carbon\Carbon::parse($u->last_login)->format('Y-m-d H:i') }}
            @else
              Never
            @endif
          </td>
          <td class="d-flex gap-1">
            <a href="{{ route('users.editPassword', $u) }}"
               class="btn btn-sm btn-outline-primary">
              Change Password
            </a>
            <form action="{{ route('users.destroy', $u) }}"
                  method="POST"
                  onsubmit="return confirm('Delete this user?')">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-muted py-4">
            No users found.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <div class="mt-3">
    {{ $users->links() }}
  </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.role-select').forEach(select => {
  let original = select.dataset.currentRole;
  select.addEventListener('change', async function() {
    const res = await fetch(this.dataset.updateUrl, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ role: this.value })
    });
    if (res.ok) {
      const json = await res.json();
      alert(json.message);
      original = this.value;
      select.dataset.currentRole = this.value;
    } else {
      alert('Failed to update role');
      this.value = original;
    }
  });
});
</script>
@endsection
