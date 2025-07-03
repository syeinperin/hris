{{-- resources/views/payroll/index.blade.php --}}
@extends('layouts.app')

@section('page_title','Payroll Summary')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Payroll Summary</h3>
    <div class="btn-group">
      {{-- Salary Rates button --}}
      <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-percent me-1"></i> Salary Rates
      </a>
      {{-- Deductions button --}}
      <a href="{{ route('deductions.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-credit-card-2-back me-1"></i> Deductions
      </a>
      {{-- Calendar button --}}
      <a href="{{ route('payroll.calendar') }}" class="btn btn-outline-secondary">
        <i class="bi bi-calendar-event me-1"></i> Calendar
      </a>
      {{-- Holidays button --}}
      <a href="{{ route('holidays.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-flag me-1"></i> Holidays
      </a>
    </div>
  </div>

  {{-- Search & Filters --}}
  <form method="GET" action="{{ route('payroll.index') }}" class="row g-3 mb-4">
    <div class="col-md-3 form-floating">
      <input
        type="date"
        name="start_date"
        class="form-control"
        id="start_date"
        value="{{ request('start_date', $startDate) }}"
      >
      <label for="start_date">From</label>
    </div>
    <div class="col-md-3 form-floating">
      <input
        type="date"
        name="end_date"
        class="form-control"
        id="end_date"
        value="{{ request('end_date', $endDate) }}"
      >
      <label for="end_date">To</label>
    </div>
    <div class="col-md-4 form-floating">
      <input
        type="text"
        name="search"
        class="form-control"
        id="search"
        placeholder="Search by name or code"
        value="{{ request('search', $search) }}"
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
          <th>Date</th>
          <th>Employee Code</th>
          <th>Employee Name</th>
          <th>Rate/hr</th>
          <th>Worked (hr)</th>
          <th>OT (hr)</th>
          <th>OT Pay</th>
          <th>SSS (Emp)</th>
          <th>PhilHealth (Emp)</th>
          <th>Pag-IBIG (Emp)</th>
          <th>Deductions</th>
          <th>Gross Pay</th>
          <th>Net Pay</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $row)
          <tr>
            <td>{{ $row['date'] }}</td>
            <td>{{ $row['employee_code'] }}</td>
            <td>{{ $row['employee_name'] }}</td>
            <td>₱{{ $row['rate_hr'] }}</td>
            <td>{{ $row['worked_hr'] }}</td>
            <td>{{ $row['ot_hr'] }}</td>
            <td>₱{{ $row['ot_pay'] }}</td>
            <td>₱{{ $row['sss'] }}</td>
            <td>₱{{ $row['philhealth'] }}</td>
            <td>₱{{ $row['pagibig'] }}</td>
            <td>₱{{ $row['deductions'] }}</td>
            <td>₱{{ $row['gross_pay'] }}</td>
            <td><strong>₱{{ $row['net_pay'] }}</strong></td>
          </tr>
        @empty
          <tr>
            <td colspan="13" class="text-center">No payroll data for the selected range.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="d-flex justify-content-center">
    {{ $rows->withQueryString()->links() }}
  </div>
</div>
@endsection
