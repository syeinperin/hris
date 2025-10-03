{{-- resources/views/offboarding/index.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Offboarding')

@push('styles')
<style>
  .ofb-card{
    border:1px solid #edf0f4;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(18,38,63,.06);
  }
  .status-pill{
    text-transform:capitalize;
    font-weight:600;
  }
  .table-sticky thead th{
    position: sticky; top: 0; background: #fff; z-index: 1;
  }
</style>
@endpush

@section('content')
<div class="container-fluid">

  {{-- Toolbar --}}
  <div class="ofb-card p-3 mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
      <div class="d-flex flex-wrap gap-2">
        @php $status = request('status'); @endphp

        <a href="{{ route('offboarding.index') }}"
           class="btn btn-outline-secondary {{ $status ? '' : 'active' }}">
          <i class="bi bi-list-ul me-1"></i> All
        </a>

        <a href="{{ route('offboarding.index', ['status' => 'pending_clearance']) }}"
           class="btn btn-outline-warning {{ $status==='pending_clearance' ? 'active' : '' }}">
          <i class="bi bi-hourglass-split me-1"></i> Pending Clearance
          @isset($counts['pending_clearance'])
            <span class="badge bg-warning text-dark ms-1">{{ $counts['pending_clearance'] }}</span>
          @endisset
        </a>

        <a href="{{ route('offboarding.index', ['status' => 'completed']) }}"
           class="btn btn-outline-success {{ $status==='completed' ? 'active' : '' }}">
          <i class="bi bi-check2-circle me-1"></i> Completed
          @isset($counts['completed'])
            <span class="badge bg-success ms-1">{{ $counts['completed'] }}</span>
          @endisset
        </a>
      </div>

      <div class="d-flex gap-2">
        {{-- optional: search box --}}
        <form method="get" class="d-flex">
          <input type="hidden" name="status" value="{{ request('status') }}">
          <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                 placeholder="Search employee / code">
        </form>

        {{-- Start Offboarding --}}
        <a href="{{ route('offboarding.create') }}" class="btn btn-primary">
          <i class="bi bi-plus-lg me-1"></i> Start Offboarding
        </a>
      </div>
    </div>
  </div>

  {{-- Table --}}
  <div class="ofb-card p-3">
    <div class="table-responsive table-sticky">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr class="table-light">
            <th>#</th>
            <th>Employee</th>
            <th>Type</th>
            <th>Reason</th>
            <th>Scheduled</th>
            <th>Status</th>
            <th class="text-end" style="width:1%;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($offboardings as $ofb)
            <tr>
              <td>{{ $ofb->id }}</td>
              <td>
                {{ $ofb->employee->employee_code }} — {{ $ofb->employee->name }}
              </td>
              <td>{{ $ofb->type ? ucfirst($ofb->type) : '—' }}</td>
              <td>{{ $ofb->reason ?: '—' }}</td>
              <td>{{ $ofb->scheduled_at ? $ofb->scheduled_at->format('d/m/Y h:i a') : '—' }}</td>
              <td>
                @php
                  $badge = match($ofb->status){
                    'pending_clearance' => 'bg-warning text-dark',
                    'completed'         => 'bg-success',
                    'cancelled'         => 'bg-secondary',
                    default             => 'bg-secondary'
                  };
                @endphp
                <span class="badge {{ $badge }} status-pill">
                  {{ str_replace('_',' ', $ofb->status) }}
                </span>
              </td>
              <td class="text-end">
                <div class="btn-group">
                  <a href="{{ route('offboarding.show', $ofb) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i>
                  </a>

                  {{-- Quick: mark pending clearance --}}
                  @if($ofb->status !== 'pending_clearance')
                    <form action="{{ route('offboarding.pendingClearance', $ofb) }}" method="POST">
                      @csrf @method('PATCH')
                      <button class="btn btn-sm btn-outline-warning" title="Pending Clearance">
                        <i class="bi bi-hourglass-split"></i>
                      </button>
                    </form>
                  @endif

                  {{-- Quick: complete --}}
                  @if($ofb->status !== 'completed')
                    <form action="{{ route('offboarding.complete', $ofb) }}" method="POST"
                          onsubmit="return confirm('Mark as completed?')">
                      @csrf @method('PATCH')
                      <button class="btn btn-sm btn-outline-success" title="Complete">
                        <i class="bi bi-check2-circle"></i>
                      </button>
                    </form>
                  @endif

                  {{-- Cancel --}}
                  <form action="{{ route('offboarding.cancel', $ofb) }}" method="POST"
                        onsubmit="return confirm('Cancel this offboarding?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-secondary" title="Cancel">
                      <i class="bi bi-x-circle"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">
                No offboarding records found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <small class="text-muted">
        Showing {{ $offboardings->firstItem() }}–{{ $offboardings->lastItem() }} of {{ $offboardings->total() }}
      </small>
      {{ $offboardings->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
  </div>

</div>
@endsection
