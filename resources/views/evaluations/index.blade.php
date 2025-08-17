@extends('layouts.app')
@section('page_title','Performance Evaluations')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="bi bi-clipboard2-check me-2"></i> Performance Evaluations
      </h4>
      <div class="d-flex align-items-center">
        {{-- New Evaluation (modal) --}}
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#evalCreateModal">
          <i class="bi bi-plus-lg me-1"></i> New Evaluation
        </button>

        {{-- Violations/Suspension goes to its own index page --}}
        <a href="{{ route('discipline.index') }}" class="btn btn-outline-danger ms-2">
          <i class="bi bi-exclamation-octagon me-1"></i> Violations / Suspension
        </a>
      </div>
    </div>

    <div class="card-body">
      {{-- Search --}}
      <form method="GET" action="{{ route('evaluations.index') }}" class="row g-2 mb-3">
        <div class="col-md-8">
          <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search employee…">
        </div>
        <div class="col-md-2">
          <button class="btn btn-outline-secondary w-100">
            <i class="bi bi-search me-1"></i> Search
          </button>
        </div>
      </form>

      {{-- Table --}}
      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Employee</th>
              <th>Period</th>
              <th>Overall %</th>
              <th>Evaluator</th>
              <th>Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
          @forelse($evaluations as $e)
            <tr>
              <td>{{ $loop->iteration + ($evaluations->currentPage()-1)*$evaluations->perPage() }}</td>
              <td>{{ $e->employee->name }}</td>
              <td>{{ $e->period_start->toDateString() }} – {{ $e->period_end->toDateString() }}</td>
              <td class="fw-semibold">{{ number_format($e->overall_score, 2) }}%</td>
              <td>{{ $e->evaluator->name ?? '—' }}</td>
              <td>
                <span class="badge {{ $e->status==='submitted'?'bg-success':'bg-secondary' }}">
                  {{ ucfirst($e->status) }}
                </span>
              </td>
              <td class="text-center">
                <a href="{{ route('evaluations.show',$e) }}" class="btn btn-sm btn-outline-dark" title="View">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('evaluations.edit',$e) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('evaluations.destroy',$e) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete this evaluation?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">No evaluations found.</td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-3">
        <small class="text-muted">
          @if($evaluations->total() > 0)
            Showing {{ $evaluations->firstItem() }}–{{ $evaluations->lastItem() }} of {{ $evaluations->total() }}
          @else
            Showing 0 of 0
          @endif
        </small>
        {{ $evaluations->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection

@push('modals')
  {{-- CREATE --}}
  <div class="modal fade" id="evalCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <form action="{{ route('evaluations.store') }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> New Evaluation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" style="max-height:70vh;overflow:auto;">
            @include('evaluations.partials.form', ['mode'=>'create'])
          </div>
          <div class="modal-footer">
            <button class="btn btn-success" type="submit">
              <i class="bi bi-check2-circle me-1"></i> Save
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endpush

@push('scripts')
  {{-- Select-all/clear for multi-employee create (if you use it) --}}
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const sel = document.getElementById('employee_ids_create');
      const selAll = document.getElementById('selectAllEmployees');
      const clrAll = document.getElementById('clearAllEmployees');
      if (sel && selAll && clrAll) {
        selAll.addEventListener('click', e => { e.preventDefault(); [...sel.options].forEach(o => o.selected = true); });
        clrAll.addEventListener('click', e => { e.preventDefault(); [...sel.options].forEach(o => o.selected = false); });
      }
    });
  </script>
@endpush
