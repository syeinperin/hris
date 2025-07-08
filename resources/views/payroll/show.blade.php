@extends('layouts.app')

@section('page_title','Payslip for '.$employee->name)

@push('styles')
  <style>
    /* Prevent wrapping so resizing grips make sense */
    #first-cutoff th, #first-cutoff td,
    #second-cutoff th, #second-cutoff td,
    #employee-loans th, #employee-loans td {
      white-space: nowrap;
      position: relative;
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
    <div>
        <a href="{{ route('payroll.index') }}" class="btn btn-secondary">
        ← Back to Payroll Summary
      </a>
    </div>
  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
      <h4>Payslip: {{ $employee->name }}</h4>
      <small>Month: {{ $month }}</small>
    </div>
    <div class="card-body">

      {{-- Cut-off 1–15 --}}
      <h5>Cut-off 1–15</h5>
      <div class="table-responsive mb-4">
        <table id="first-cutoff" class="table table-bordered align-middle w-100"
               style="table-layout:auto;">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Worked (hr)</th>
              <th>OT (hr)</th>
              <th>Gross</th>
              <th>SSS</th>
              <th>PhilHealth</th>
              <th>Pag-IBIG</th>
              <th>Deductions</th>
              <th>Loan</th>
              <th>Net</th>
            </tr>
          </thead>
          <tbody>
            @foreach($firstRows as $r)
              <tr>
                <td>{{ $r['date'] }}</td>
                <td>{{ $r['worked_hr'] }}</td>
                <td>{{ $r['ot_hr'] }}</td>
                <td>₱{{ $r['gross'] }}</td>
                <td>₱{{ $r['sss'] }}</td>
                <td>₱{{ $r['philhealth'] }}</td>
                <td>₱{{ $r['pagibig'] }}</td>
                <td>₱{{ $r['deductions'] }}</td>
                <td>₱{{ $r['loan'] }}</td>
                <td>₱{{ $r['net'] }}</td>
              </tr>
            @endforeach
            <tr class="table-light">
              <th colspan="3">Totals</th>
              <th>₱{{ number_format($summary['first']['gross'],2) }}</th>
              <th colspan="3"></th>
              <th>₱{{ number_format($summary['first']['deductions'],2) }}</th>
              <th>₱{{ number_format($summary['first']['loan'],2) }}</th>
              <th>₱{{ number_format($summary['first']['net'],2) }}</th>
            </tr>
          </tbody>
        </table>
      </div>

      {{-- Cut-off 16–end --}}
      <h5>Cut-off 16–{{ \Carbon\Carbon::parse("$month-01")->endOfMonth()->day }}</h5>
      <div class="table-responsive mb-4">
        <table id="second-cutoff" class="table table-bordered align-middle w-100"
               style="table-layout:auto;">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Worked (hr)</th>
              <th>OT (hr)</th>
              <th>Gross</th>
              <th>SSS</th>
              <th>PhilHealth</th>
              <th>Pag-IBIG</th>
              <th>Deductions</th>
              <th>Net</th>
            </tr>
          </thead>
          <tbody>
            @foreach($secondRows as $r)
              <tr>
                <td>{{ $r['date'] }}</td>
                <td>{{ $r['worked_hr'] }}</td>
                <td>{{ $r['ot_hr'] }}</td>
                <td>₱{{ $r['gross'] }}</td>
                <td>₱{{ $r['sss'] }}</td>
                <td>₱{{ $r['philhealth'] }}</td>
                <td>₱{{ $r['pagibig'] }}</td>
                <td>₱{{ $r['deductions'] }}</td>
                <td>₱{{ $r['net'] }}</td>
              </tr>
            @endforeach
            <tr class="table-light">
              <th colspan="3">Totals</th>
              <th>₱{{ number_format($summary['second']['gross'],2) }}</th>
              <th colspan="3"></th>
              <th>₱{{ number_format($summary['second']['deductions'],2) }}</th>
              <th>₱{{ number_format($summary['second']['net'],2) }}</th>
            </tr>
          </tbody>
        </table>
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
      $('#first-cutoff, #second-cutoff, #employee-loans').colResizable({
        liveDrag: true,
        gripInnerHtml: "<div class='grip'></div>",
        draggingClass: "dragging"
      });
    });
  </script>
@endpush
