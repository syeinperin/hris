@extends('layouts.app')
@section('page_title','Disciplinary Actions')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-white">
      <h4 class="mb-0"><i class="bi bi-exclamation-octagon me-2"></i> Violations / Suspensions</h4>
      <div>
        <a href="{{ route('discipline.create') }}" class="btn btn-primary">
          <i class="bi bi-plus-lg me-1"></i> New Action
        </a>
      </div>
    </div>

    <div class="card-body">
      <form class="row g-2 mb-3" method="GET" action="{{ route('discipline.index') }}">
        <div class="col-md-4">
          <select name="employee_id" class="form-select">
            <option value="">All Employees</option>
            @foreach($employees as $id=>$name)
              <option value="{{ $id }}" {{ request('employee_id')==$id?'selected':'' }}>{{ $name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <select name="type" class="form-select">
            <option value="">All Types</option>
            <option value="violation" {{ request('type')==='violation'?'selected':'' }}>Violation</option>
            <option value="suspension" {{ request('type')==='suspension'?'selected':'' }}>Suspension</option>
          </select>
        </div>
        <div class="col-md-3">
          <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <option value="active"   {{ request('status')==='active'?'selected':'' }}>Active</option>
            <option value="resolved" {{ request('status')==='resolved'?'selected':'' }}>Resolved</option>
          </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button class="btn btn-outline-secondary flex-fill"><i class="bi bi-funnel me-1"></i> Filter</button>
          <a href="{{ route('discipline.index') }}" class="btn btn-outline-secondary flex-fill">Reset</a>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>When</th>
              <th>Employee</th>
              <th>Type</th>
              <th>Category</th>
              <th>Severity</th>
              <th>Points</th>
              <th>Reason</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($actions as $a)
              <tr>
                <td class="text-nowrap">{{ $a->created_at->toDateString() }}</td>
                <td>{{ $a->employee->name ?? '—' }}</td>
                <td>
                  <span class="badge {{ $a->action_type==='suspension'?'bg-danger':'bg-warning text-dark' }}">
                    {{ ucfirst($a->action_type) }}
                  </span>
                </td>
                <td>{{ $a->category ?? '—' }}</td>
                <td>{{ ucfirst($a->severity) }}</td>
                <td>{{ $a->points ?? '—' }}</td>
                <td class="text-truncate" style="max-width:260px;" title="{{ $a->reason }}">{{ $a->reason }}</td>
                <td>
                  <span class="badge {{ $a->status==='active'?'bg-primary':'bg-secondary' }}">{{ ucfirst($a->status) }}</span>
                </td>
                <td class="text-end">
                  {{-- Print letter --}}
                  <a href="{{ route('discipline.pdf', $a) }}" target="_blank"
                     class="btn btn-sm btn-outline-secondary" title="Print Letter">
                    <i class="bi bi-printer"></i>
                  </a>

                  {{-- Resolve --}}
                  @if($a->status==='active')
                  <form action="{{ route('discipline.resolve',$a) }}" method="POST" class="d-inline">
                    @csrf @method('PUT')
                    <button class="btn btn-sm btn-outline-success" title="Mark Resolved">
                      <i class="bi bi-check2"></i>
                    </button>
                  </form>
                  @endif

                  {{-- Delete --}}
                  <form action="{{ route('discipline.destroy',$a) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this record?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="9" class="text-center text-muted py-4">No records.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
          @if($actions->total()>0)
            Showing {{ $actions->firstItem() }}–{{ $actions->lastItem() }} of {{ $actions->total() }}
          @else
            Showing 0 of 0
          @endif
        </small>
        {{ $actions->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection
