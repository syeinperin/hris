@extends('layouts.app')

@section('page_title','Approvals')

@section('content')
<div class="container-fluid py-4">
  {{-- Pending User Approvals --}}
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex align-items-center">
      <h4 class="mb-0"><i class="bi bi-person-check me-2"></i> Pending User Approvals</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Requested</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pendingUsers as $approval)
              @php $user = $approval->approvable; @endphp
              <tr>
                <td>{{ $user?->name }}</td>
                <td>{{ $user?->email }}</td>
                <td>{{ $approval->created_at->format('Y-m-d') }}</td>
                <td class="text-end">
                  <form class="d-inline" method="POST"
                        action="{{ route('approvals.approve', ['t'=>'user','id'=>$user->id]) }}">
                    @csrf
                    <button class="btn btn-sm btn-success">Approve</button>
                  </form>
                  <form class="d-inline" method="POST"
                        action="{{ route('approvals.destroy', ['t'=>'user','id'=>$user->id]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Reject</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-4">
                  No pending user requests.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Pending Leave Requests --}}
  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex align-items-center">
      <h4 class="mb-0"><i class="bi bi-calendar-check me-2"></i> Pending Leave Requests</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Employee</th>
              <th>From</th>
              <th>To</th>
              <th>Reason</th>
              <th>Requested</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pendingLeaves as $approval)
              @php $leave = $approval->approvable; @endphp
              <tr>
                <td>{{ optional($leave?->user)->name ?? '—' }}</td>
                <td>{{ optional($leave?->start_date)->toDateString() }}</td>
                <td>{{ optional($leave?->end_date)->toDateString() }}</td>
                <td>{{ \Illuminate\Support\Str::limit($leave?->reason, 30) }}</td>
                <td>{{ $approval->created_at->format('Y-m-d') }}</td>
                <td class="text-end">
                  <form class="d-inline" method="POST"
                        action="{{ route('approvals.approve', ['t'=>'leave','id'=>$leave->id]) }}">
                    @csrf
                    <button class="btn btn-sm btn-success">Approve</button>
                  </form>
                  <form class="d-inline" method="POST"
                        action="{{ route('approvals.destroy', ['t'=>'leave','id'=>$leave->id]) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Reject</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  No pending leave requests.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
