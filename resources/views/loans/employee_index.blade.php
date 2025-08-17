{{-- resources/views/loans/employee_index.blade.php --}}
@extends('layouts.app')

@section('page_title', 'My Loans')

@section('content')
<div class="container-fluid py-4">
  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <h4 class="mb-0">
        <i class="bi bi-wallet2 me-2"></i>
        My Loans
      </h4>
    </div>
    <div class="card-body p-0">
      @if($loans->isEmpty())
        <div class="alert alert-info m-4">
          You don’t have any loans at the moment.
        </div>
      @else
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Type</th>
                <th>Amount</th>
                <th>Outstanding</th>
                <th>Deduction / mo.</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($loans as $loan)
                <tr>
                  <td>{{ $loan->type }}</td>
                  <td>{{ number_format($loan->amount, 2) }}</td>
                  <td>{{ number_format($loan->outstanding_balance, 2) }}</td>
                  <td>{{ number_format($loan->monthly_deduction, 2) }}</td>
                  <td>{{ ucfirst($loan->status) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
    <div class="card-footer">
      {{ $loans->links() }}
    </div>
  </div>
</div>
@endsection
