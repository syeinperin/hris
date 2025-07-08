{{-- resources/views/payroll/index.blade.php --}}
@extends('layouts.app')

@section('page_title','Payroll Summary')

@push('styles')
  <style>
    /* Prevent wrapping so resizing makes sense */
    #payroll-table th,
    #payroll-table td {
      white-space: nowrap;
      position: relative;
    }
    /* Fix widths on the first two columns */
    #payroll-table th.fixed-code,
    #payroll-table td.fixed-code {
      width: 120px;
    }
    #payroll-table th.fixed-name,
    #payroll-table td.fixed-name {
      width: 200px;
    }
    /* Grip styling for colResizable */
    .grip {
      position: absolute;
      top: 0;
      right: -2px;
      width: 5px;
      height: 100%;
      cursor: col-resize;
    }
    .dragging {
      background: rgba(0, 123, 255, 0.1);
    }
  </style>
@endpush

@section('content')
<div class="container-fluid">
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payroll Summary</h4>
      <div>
        <a href="{{ route('designations.index') }}" class="btn btn-outline-secondary btn-sm me-2">
          <i class="bi bi-percent me-1"></i>Salary Rates
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
          <input type="date" name="date" class="form-control"
                 value="{{ request('date', $date) }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Search</label>
          <input type="text" name="search" class="form-control"
                 placeholder="Name or code…" value="{{ request('search', $search) }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-search me-1"></i>Filter
          </button>
        </div>
      </form>

      {{-- PAYROLL TABLE --}}
      <div class="table-responsive mb-4">
        <table id="payroll-table" class="table table-hover table-bordered align-middle">
          <colgroup>
            <col class="fixed-code">
            <col class="fixed-name">
            {{-- 12 more flexible cols --}}
            @for($i=0; $i<12; $i++)
              <col>
            @endfor
          </colgroup>
          <thead class="table-light">
            <tr>
              <th class="fixed-code">Code</th>
              <th class="fixed-name">Name</th>
              <th>Rate/hr</th>
              <th>Worked (hr)</th>
              <th>OT (hr)</th>
              <th>OT Pay</th>
              <th>SSS</th>
              <th>PhilHealth</th>
              <th>Pag-IBIG</th>
              <th>Late Ded</th>
              <th>Loan Ded</th>
              <th>Total Ded</th>
              <th>Gross</th>
              <th>Net</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $row)
              <tr>
                <td class="fixed-code">{{ $row['employee_code'] }}</td>
                <td class="fixed-name">{{ $row['employee_name'] }}</td>
                <td>₱{{ $row['rate_hr'] }}</td>
                <td>{{ $row['worked_hr'] }}</td>
                <td>{{ $row['ot_hr'] }}</td>
                <td>₱{{ $row['ot_pay'] }}</td>
                <td>₱{{ $row['sss'] }}</td>
                <td>₱{{ $row['philhealth'] }}</td>
                <td>₱{{ $row['pagibig'] }}</td>
                <td>₱{{ $row['late_deduction'] }}</td>
                <td>₱{{ $row['loan_deduction'] }}</td>
                <td>₱{{ $row['deductions'] }}</td>
                <td>₱{{ $row['gross_pay'] }}</td>
                <td><strong>₱{{ $row['net_pay'] }}</strong></td>
                <td>
                  <a href="{{ route('payroll.show', $row['employee_id']) }}?month={{ \Str::substr($date,0,7) }}"
                     class="btn btn-sm btn-primary">View</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="15" class="text-center text-muted">
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
          Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }}
        </small>
        {{ $rows->withQueryString()->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  {{-- jQuery --}}
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  {{-- colResizable --}}
  <script src="https://cdn.jsdelivr.net/npm/colresizable@1.6.0/colResizable-1.6.min.js"></script>
  <script>
    $(function(){
      $('#payroll-table').colResizable({
        liveDrag: true,
        gripInnerHtml: "<div class='grip'></div>",
        draggingClass: "dragging"
      });
    });
  </script>
@endpush
