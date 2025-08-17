@extends('layouts.app')

@section('page_title','Payroll Summary')

@push('styles')
  <style>
    .summary-table-wrapper { overflow-x: hidden; }
  </style>
@endpush

@section('content')
<div class="container-fluid">
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">
        <i class="bi bi-cash-stack me-2"></i>Payroll Summary
      </h4>
      <div class="d-flex gap-2">
        <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-percent me-1"></i>Salary Rates
        </a>
        <a href="{{ route('payroll.manual') }}" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-pencil-square me-1"></i>Manual Payroll
        </a>
        <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-journal-medical me-1"></i>Loans
        </a>
      </div>
    </div>

    <div class="card-body">
      {{-- FILTER --}}
      <form method="GET" action="{{ route('payroll.index') }}" class="row g-3 mb-4">
        <div class="col-md-3">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="{{ request('date', $date) }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" name="search" class="form-control" placeholder="Name or code…" value="{{ request('search', $search) }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-search me-1"></i>Filter
          </button>
        </div>
      </form>

      {{-- SUMMARY TABLE --}}
      <div class="summary-table-wrapper mb-4">
        <table class="table table-hover table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Code</th>
              <th>Name</th>
              <th class="text-end">Net Pay</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $row)
              <tr>
                <td>{{ $row['employee_code'] }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td class="text-end">₱{{ $row['net_pay'] }}</td>
                <td>
                  <a href="{{ route('payroll.show',$row['employee_id']) }}?month={{ substr($date,0,7) }}"
                     class="btn btn-sm btn-primary">
                    View
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted">
                  No payroll data for {{ $date }}.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- PAGINATION --}}
      <div class="d-flex justify-content-between align-items-center mb-5">
        <small class="text-muted">
          Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }}
          of {{ $rows->total() }}
        </small>
        {{ $rows->withQueryString()->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection
