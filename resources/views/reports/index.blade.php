@extends('layouts.app')

@section('page_title', 'Reports')

@section('content')
<div class="container">
  <h3 class="mb-4">Reports</h3>

  <div class="row g-4 mb-4">
    {{-- Employee List --}}
    <div class="col-md-6">
      <a href="{{ route('reports.employees.index') }}"
         class="card h-100 text-decoration-none text-dark">
        <div class="card-body text-center">
          <i class="bi bi-file-earmark-person-fill fs-1"></i>
          <h5 class="mt-3">Employee List</h5>
          <small class="text-muted">Manage CSV / PDF / Certificate</small>
        </div>
      </a>
    </div>

    {{-- Performance Evaluations --}}
    <div class="col-md-6">
      <form action="{{ route('reports.performance') }}" method="GET" class="card h-100 p-3">
        <div class="card-body text-center">
          <i class="bi bi-graph-up-arrow fs-1"></i>
          <h5 class="mt-3">Performance Evaluations</h5>
          <small class="text-muted">Download CSV</small>
          <div class="d-flex justify-content-center gap-2 my-3">
            <input type="date" name="from" class="form-control" value="{{ now()->toDateString() }}">
            <input type="date" name="to"   class="form-control" value="{{ now()->toDateString() }}">
          </div>
          <button class="btn btn-outline-primary w-100">Download CSV</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-4">
    {{-- Attendance --}}
    <div class="col-md-4">
      <form action="{{ route('reports.attendance') }}" method="GET" class="card h-100 p-3">
        <div class="card-body text-center">
          <i class="bi bi-calendar-check-fill fs-1"></i>
          <h5 class="mt-3">Attendance</h5>
          <small class="text-muted">Download CSV</small>
          <div class="d-flex justify-content-center gap-2 my-3">
            <input type="date" name="from" class="form-control" value="{{ now()->toDateString() }}">
            <input type="date" name="to"   class="form-control" value="{{ now()->toDateString() }}">
          </div>
          <button class="btn btn-outline-primary w-100">Download CSV</button>
        </div>
      </form>
    </div>

    {{-- Payroll Summary --}}
    <div class="col-md-4">
      <form action="{{ route('reports.payroll') }}" method="GET" class="card h-100 p-3">
        <div class="card-body text-center">
          <i class="bi bi-cash-stack fs-1"></i>
          <h5 class="mt-3">Payroll Summary</h5>
          <small class="text-muted">Based on hourly rate</small>
          <div class="d-flex justify-content-center gap-2 my-3">
            <input type="date" name="from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
            <input type="date" name="to"   class="form-control" value="{{ now()->endOfMonth()->toDateString() }}">
          </div>
          <button class="btn btn-outline-primary w-100">Download CSV</button>
        </div>
      </form>
    </div>

    {{-- Payslips --}}
    <div class="col-md-4">
      <form action="{{ route('reports.payslips') }}" method="GET" class="card h-100 p-3">
        <div class="card-body text-center">
          <i class="bi bi-wallet2 fs-1"></i>
          <h5 class="mt-3">Payslips</h5>
          <small class="text-muted">Download CSV</small>
          <div class="d-flex justify-content-center gap-2 my-3">
            <input type="date" name="from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
            <input type="date" name="to"   class="form-control" value="{{ now()->endOfMonth()->toDateString() }}">
          </div>
          <button class="btn btn-outline-primary w-100">Download CSV</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
