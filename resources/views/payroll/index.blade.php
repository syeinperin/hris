@extends('layouts.app')

@section('page_title','Payroll Summary')

@section('content')
<div class="container-fluid">
  <h3 class="mb-4">Payroll Summary</h3>

  {{-- Search & Filters --}}
  <form method="GET" action="{{ route('payroll.index') }}" class="row g-3 mb-4">
    <div class="col-md-3 form-floating">
      <input
        type="date"
        name="start_date"
        class="form-control"
        id="start_date"
        value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}"
      >
      <label for="start_date">From</label>
    </div>
    <div class="col-md-3 form-floating">
      <input
        type="date"
        name="end_date"
        class="form-control"
        id="end_date"
        value="{{ request('end_date', now()->endOfMonth()->toDateString()) }}"
      >
      <label for="end_date">To</label>
    </div>
    <div class="col-md-4 form-floating">
      <input
        type="text"
        name="search"
        class="form-control"
        id="search"
        placeholder="Search..."
        value="{{ request('search') }}"
      >
      <label for="search">Search</label>
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-search me-1"></i> Filter
      </button>
    </div>
  </form>

  {{-- Table --}}
  <div class="table-responsive mb-3">
    <table class="table table-striped table-bordered align-middle">
      <thead class="table-dark">
        <tr>
          <th>Employee</th>
          <th>Rate/hr</th>
          <th>Worked (hr)</th>
          <th>OT (hr)</th>
          <th>OT Pay</th>
          <th>Deductions</th>
          <th>Gross Pay</th>   {{-- moved here --}}
          <th>Net Pay</th>
        </tr>
      </thead>
      <tbody>
        @foreach($employees as $emp)
        <tr>
          <td>{{ $emp->name }}</td>
          <td>₱{{ number_format($emp->rate_per_hour, 2) }}</td>
          <td>{{ number_format($emp->worked_hours, 2) }}</td>
          <td>{{ number_format($emp->overtime_hours, 2) }}</td>
          <td>₱{{ number_format($emp->overtime_pay, 2) }}</td>
          <td>₱{{ number_format($emp->total_deduction, 2) }}</td>
          <td>₱{{ number_format($emp->gross_pay, 2) }}</td> {{-- moved here --}}
          <td><strong>₱{{ number_format($emp->net_pay, 2) }}</strong></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="d-flex justify-content-center">
    {{ $employees->withQueryString()->links() }}
  </div>
</div>
@endsection
