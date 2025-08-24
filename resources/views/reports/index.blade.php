@extends('layouts.app')

@section('page_title', 'Reports')

@push('styles')
<style>
  .reports-hero { display:flex; align-items:center; gap:.75rem; }
  .reports-hero h3 { margin:0; font-weight:700; letter-spacing:.2px; }
  .reports-hero small { color:#6c757d; }
  .report-card { border:1px solid rgba(0,0,0,.05); border-radius:14px; transition:.18s transform, .18s box-shadow, .18s border-color; }
  .report-card:hover { transform:translateY(-2px); box-shadow:0 10px 22px rgba(0,0,0,.06); border-color:rgba(0,0,0,.08); }
  .report-card .card-body { padding:1.2rem 1.2rem 1.1rem; }
  .icon-badge { width:58px;height:58px;border-radius:50%;display:inline-grid;place-items:center;margin:.25rem auto .35rem;color:#0d6efd;background:rgba(13,110,253,.08); }
  .icon-badge .bi { font-size:1.6rem; }
  .report-title { font-weight:700;margin:.25rem 0 .15rem; }
  .report-sub { color:#6c757d;font-size:.9rem; }
  .date-row .input-group-text{ background:#fff;border-right:0; }
  .date-row .form-control{ border-left:0; }
  .btn-outline-primary.w-100 { font-weight:600; }
</style>
@endpush

@section('content')
<div class="container-fluid">
  <div class="reports-hero mb-3">
    <h3>Reports</h3>
    <small>Export CSV, PDFs, and summaries</small>
  </div>

  <div class="row g-4 mb-4">
    {{-- Employee List --}}
    <div class="col-md-6">
      <a href="{{ route('reports.employees.index') }}" class="card report-card h-100 text-decoration-none text-dark">
        <div class="card-body text-center">
          <div class="icon-badge"><i class="bi bi-file-earmark-person"></i></div>
          <div class="report-title">Employee List</div>
          <div class="report-sub">Manage CSV / PDF / Certificate</div>
        </div>
      </a>
    </div>

    {{-- Performance Evaluations (link to page) --}}
    <div class="col-md-6">
      <a href="{{ route('reports.performance') }}" class="card report-card h-100 text-decoration-none text-dark">
        <div class="card-body text-center">
          <div class="icon-badge"><i class="bi bi-graph-up-arrow"></i></div>
          <div class="report-title">Performance Evaluations</div>
          <div class="report-sub">View report & export CSV</div>
        </div>
      </a>
    </div>
  </div>

  <div class="row g-4">
    {{-- Attendance --}}
    <div class="col-lg-4">
      <form action="{{ route('reports.attendance') }}" method="GET" class="card report-card h-100">
        <div class="card-body text-center">
          <div class="icon-badge"><i class="bi bi-calendar-check"></i></div>
          <div class="report-title">Attendance</div>
          <div class="report-sub">Download CSV</div>

          <div class="row g-2 justify-content-center my-3 date-row">
            <div class="col-6">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                <input type="date" name="from" class="form-control" value="{{ now()->toDateString() }}">
              </div>
            </div>
            <div class="col-6">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                <input type="date" name="to" class="form-control" value="{{ now()->toDateString() }}">
              </div>
            </div>
          </div>

          <button class="btn btn-outline-primary w-100">
            <i class="bi bi-download me-1"></i>Download CSV
          </button>
        </div>
      </form>
    </div>

    {{-- Payroll Summary --}}
    <div class="col-lg-4">
      <form action="{{ route('reports.payroll') }}" method="GET" class="card report-card h-100">
        <div class="card-body text-center">
          <div class="icon-badge"><i class="bi bi-cash-stack"></i></div>
          <div class="report-title">Payroll Summary</div>
          <div class="report-sub">Based on hourly rate</div>

          <div class="row g-2 justify-content-center my-3 date-row">
            <div class="col-6">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                <input type="date" name="from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
              </div>
            </div>
            <div class="col-6">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                <input type="date" name="to" class="form-control" value="{{ now()->endOfMonth()->toDateString() }}">
              </div>
            </div>
          </div>

          <button class="btn btn-outline-primary w-100">
            <i class="bi bi-download me-1"></i>Download CSV
          </button>
        </div>
      </form>
    </div>

    {{-- Payslips --}}
    <div class="col-lg-4">
      <div class="card report-card h-100">
        <div class="card-body text-center d-flex flex-column">
          <div class="icon-badge"><i class="bi bi-wallet2"></i></div>
          <div class="report-title">Payslips</div>
          <div class="report-sub">Download CSV or per-employee PDF</div>

          {{-- CSV form --}}
          <form action="{{ route('reports.payslips') }}" method="GET" class="mt-2">
            <div class="row g-2 justify-content-center my-3 date-row">
              <div class="col-6">
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                  <input type="date" name="from" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
                </div>
              </div>
              <div class="col-6">
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                  <input type="date" name="to" class="form-control" value="{{ now()->endOfMonth()->toDateString() }}">
                </div>
              </div>
            </div>

            <button class="btn btn-outline-primary w-100">
              <i class="bi bi-download me-1"></i>Download CSV
            </button>
          </form>

          <hr class="my-3 w-75 mx-auto">

          {{-- NEW: link to per-employee PDF page --}}
          <a href="{{ route('reports.payslips.list') }}"
             class="btn btn-primary w-100 mt-auto">
            <i class="bi bi-file-earmark-pdf me-1"></i> Per-employee PDF
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
