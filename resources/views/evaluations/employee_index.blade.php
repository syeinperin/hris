@extends('layouts.app')
@section('page_title','My Evaluations')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0"><i class="bi bi-clipboard2-check me-2"></i> My Evaluations</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Period</th>
              <th>Overall %</th>
              <th>Evaluator</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($evaluations as $e)
              <tr>
                <td>{{ $loop->iteration + ($evaluations->currentPage()-1)*$evaluations->perPage() }}</td>
                <td>{{ $e->period_start->toDateString() }} – {{ $e->period_end->toDateString() }}</td>
                <td class="fw-semibold">{{ number_format($e->overall_score,2) }}%</td>
                <td>{{ $e->evaluator->name ?? '—' }}</td>
                <td>
                  {{-- Link hits the show route, which returns this same page with $showEval set --}}
                  <a href="{{ route('my.evaluations.show',$e) }}" class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-eye"></i> View
                  </a>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">No evaluations yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      {{ $evaluations->links('pagination::bootstrap-5') }}
    </div>
  </div>
</div>
@endsection

@push('modals')
  @isset($showEval)
    <div class="modal fade" id="myEvalShowModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-clipboard2-check me-2"></i>
              Evaluation — {{ $showEval->period_start->toDateString() }} to {{ $showEval->period_end->toDateString() }}
            </h5>
            <span class="badge {{ $showEval->status === 'submitted' ? 'bg-success' : 'bg-secondary' }}">
              {{ ucfirst($showEval->status) }}
            </span>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" style="max-height:70vh; overflow:auto;">
            @include('evaluations.partials.show', ['evaluation'=>$showEval])
          </div>
          <div class="modal-footer">
            <a href="{{ route('my.evaluations.index') }}" class="btn btn-outline-secondary">Close</a>
          </div>
        </div>
      </div>
    </div>
  @endisset
@endpush

@push('scripts')
  @isset($showEval)
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        new bootstrap.Modal(document.getElementById('myEvalShowModal')).show();
      });
    </script>
  @endisset
@endpush
