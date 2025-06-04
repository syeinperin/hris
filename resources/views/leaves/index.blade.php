@extends('layouts.app')

@section('page_title','My Leave Requests')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Leave Requests</h1>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#fileLeaveModal" id="newLeaveBtn">
      + New Request
    </button>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>Type</th>
          <th>From</th>
          <th>To</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($requests as $req)
        <tr>
          <td>{{ $types[$req->leave_type] }}</td>
          <td>{{ $req->start_date->format('Y-m-d H:i:s') }}</td>
          <td>{{ $req->end_date->format('Y-m-d H:i:s') }}</td>
          <td>{{ Str::limit($req->reason, 30, '…') }}</td>
          <td>
            <span class="badge
              @if($req->status==='pending') bg-warning
              @elseif($req->status==='approved') bg-success
              @else bg-danger @endif">
              {{ ucfirst($req->status) }}
            </span>
          </td>
          <td>{{ $req->created_at->format('Y-m-d') }}</td>
          <td class="d-flex gap-1">
            @if($req->status==='pending')
              <button class="btn btn-sm btn-outline-primary editLeaveBtn" data-id="{{ $req->id }}">
                Edit
              </button>
              <form action="{{ route('leaves.destroy', $req->id) }}"
                    method="POST"
                    onsubmit="return confirm('Delete this request?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-muted py-4">
            You have no leave requests.
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{ $requests->links() }}
</div>

{{-- File/Edit Modal --}}
<div class="modal fade" id="fileLeaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" id="leaveForm">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">
      <div class="modal-header">
        <h5 class="modal-title" id="fileLeaveModalLabel">New Leave Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        {{-- leave_type --}}
        <div class="mb-3">
          <label class="form-label">Leave Type</label>
          <select name="leave_type" id="leave_type" class="form-select" required>
            <option value="">Choose…</option>
            @foreach($types as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        {{-- dates --}}
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">From</label>
            <input type="date" name="start_date" id="start_date" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">To</label>
            <input type="date" name="end_date" id="end_date" class="form-control" required>
          </div>
        </div>
        {{-- reason --}}
        <div class="mb-3">
          <label class="form-label">Reason <small class="text-muted">(optional)</small></label>
          <textarea name="reason" id="reason" rows="3" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button"    class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit"    class="btn btn-primary"            id="submitBtn">Submit Request</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
  const leaveModal = new bootstrap.Modal(document.getElementById('fileLeaveModal'));
  const form        = document.getElementById('leaveForm');
  const labelEl     = document.getElementById('fileLeaveModalLabel');
  const methodInput = document.getElementById('formMethod');
  const submitBtn   = document.getElementById('submitBtn');

  // Setup for New
  document.getElementById('newLeaveBtn').addEventListener('click', () => {
    form.action       = "{{ route('leaves.store') }}";
    methodInput.value = 'POST';
    labelEl.textContent  = 'New Leave Request';
    submitBtn.textContent = 'Submit Request';
    form.reset();
  });

  // Setup for Edit
  document.querySelectorAll('.editLeaveBtn').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;
      const res = await fetch(`/leaves/${id}/edit`);
      if (!res.ok) return alert('Could not load leave data.');

      const { leave } = await res.json();

      form.action         = `/leaves/${id}`;
      methodInput.value   = 'PUT';
      labelEl.textContent = 'Edit Leave Request';
      submitBtn.textContent = 'Save Changes';

      // populate
      document.getElementById('leave_type').value = leave.leave_type;
      document.getElementById('start_date').value = leave.start_date.split(' ')[0];
      document.getElementById('end_date').value   = leave.end_date.split(' ')[0];
      document.getElementById('reason').value     = leave.reason;

      leaveModal.show();
    });
  });
</script>
@endsection
