@extends('layouts.app')

@section('page_title','My Loans')

@section('content')
<div class="container-fluid">
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
      <h4 class="mb-0"><i class="bi bi-wallet2 me-2"></i> My Loans</h4>
    </div>
    <div class="card-body">
      {{-- Filter by status --}}
      <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">-- All statuses --</option>
            <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
            <option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option>
            <option value="defaulted" {{ request('status')=='defaulted'?'selected':'' }}>Defaulted</option>
          </select>
        </div>
      </form>

      {{-- Loans Table --}}
      <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Reference</th>
              <th>Type</th>
              <th>Plan</th>
              <th>Principal</th>
              <th>Monthly</th>
              <th>Next Due</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($loans as $loan)
              <tr>
                <td>{{ $loop->iteration + ($loans->currentPage()-1)*$loans->perPage() }}</td>
                <td>{{ $loan->reference_no }}</td>
                <td>{{ $loan->loanType->name }}</td>
                <td>{{ $loan->plan->name }}</td>
                <td>{{ number_format($loan->principal_amount,2) }}</td>
                <td>{{ number_format($loan->monthly_amount,2) }}</td>
                <td>{{ $loan->next_payment_date?->format('Y-m-d') }}</td>
                <td>
                  <span class="badge
                    {{ $loan->status=='active'    ? 'bg-primary':'' }}
                    {{ $loan->status=='paid'      ? 'bg-success':'' }}
                    {{ $loan->status=='defaulted' ? 'bg-danger':'' }}">
                    {{ ucfirst($loan->status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  You have no loans.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      <div class="d-flex justify-content-between align-items-center mt-3">
        @if($loans->total())
          <small class="text-muted">
            Showing {{ $loans->firstItem() }}–{{ $loans->lastItem() }} of {{ $loans->total() }}
          </small>
        @endif
        {{ $loans->withQueryString()->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection
