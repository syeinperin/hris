@extends('layouts.app')

@section('page_title','Payslip for '.$employee->name)

@push('styles')
  <style>
    #first-cutoff th, #first-cutoff td,
    #second-cutoff th, #second-cutoff td {
      white-space: nowrap;
      position: relative;
    }
    .grip { position:absolute; top:0; right:-2px; width:5px; height:100%; cursor:col-resize; }
    .dragging { background: rgba(0,123,255,.1) !important; }
  </style>
@endpush

@section('content')
<div class="container-fluid">
  <div class="mb-3">
    <a href="{{ route('payroll.index') }}" class="btn btn-secondary">← Back to Payroll Summary</a>
  </div>

  {{-- CUT-OFF 1–15 --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-white">
      <h4>Cut-off 1–15</h4>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive mb-3">
        <table id="first-cutoff" class="table table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Worked (hr)</th>
              <th>OT Pay</th>
              <th>OT (hr)</th>
              <th>ND Pay</th>
              <th>ND (hr)</th>
              <th>Holiday Pay</th>
              <th>Late Ded</th>
              <th>Personal Loan Ded</th>
              <th>Govt Ded</th>
              <th>Gross</th>
              <th>Net</th>
            </tr>
          </thead>
          <tbody>
            @foreach($firstRows as $r)
              <tr>
                <td>{{ $r['date'] }}</td>
                <td>{{ $r['worked_hr'] }}</td>
                {{-- DAILY ROWS ARE PRE-FORMATTED STRINGS FROM CONTROLLER --}}
                <td>₱{{ $r['ot_pay'] }}</td>
                <td>{{ $r['ot_hr'] }}</td>
                <td>₱{{ $r['nd_pay'] }}</td>
                <td>{{ $r['nd_hr'] }}</td>
                <td>₱{{ $r['holiday_pay'] }}</td>
                <td>₱{{ $r['late'] }}</td>
                <td>₱{{ $r['loan'] }}</td>
                <td>₱{{ $r['govt'] }}</td>
                <td>₱{{ $r['gross'] }}</td>
                <td>₱{{ $r['net'] }}</td>
              </tr>
            @endforeach

            {{-- TOTALS (1–15) --}}
            <tr class="table-secondary fw-semibold">
              <td>Total</td>
              <td>{{ $firstTotals['worked_hr'] }}</td>
              <td>₱{{ number_format($firstTotals['ot_pay'], 2) }}</td>
              <td>{{ $firstTotals['ot_hr'] }}</td>
              <td>₱{{ number_format($firstTotals['nd_pay'], 2) }}</td>
              <td>{{ $firstTotals['nd_hr'] }}</td>
              <td>₱{{ number_format($firstTotals['holiday_pay'], 2) }}</td>
              <td>₱{{ number_format($firstTotals['late'], 2) }}</td>
              <td>₱{{ number_format($firstTotals['loan'], 2) }}</td>
              <td>₱{{ number_format($firstTotals['govt'], 2) }}</td>
              <td>₱{{ number_format($firstTotals['gross'], 2) }}</td>
              <td>₱{{ number_format($firstTotals['net'], 2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- CUT-OFF 16–END --}}
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-white">
      <h4>Cut-off 16–{{ \Carbon\Carbon::parse("$month-01")->endOfMonth()->day }}</h4>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive mb-3">
        <table id="second-cutoff" class="table table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Date</th>
              <th>Worked (hr)</th>
              <th>Gross</th>
              <th>OT Pay</th>
              <th>OT (hr)</th>
              <th>ND Pay</th>
              <th>ND (hr)</th>
              <th>Holiday Pay</th>
              <th>Late Ded</th>
              <th>Personal Loan Ded</th>
              <th>Govt Ded</th>
              <th>Net</th>
            </tr>
          </thead>
          <tbody>
            @foreach($secondRows as $r)
              <tr>
                <td>{{ $r['date'] }}</td>
                <td>{{ $r['worked_hr'] }}</td>
                {{-- DAILY ROWS ARE PRE-FORMATTED STRINGS FROM CONTROLLER --}}
                <td>₱{{ $r['gross'] }}</td>
                <td>₱{{ $r['ot_pay'] }}</td>
                <td>{{ $r['ot_hr'] }}</td>
                <td>₱{{ $r['nd_pay'] }}</td>
                <td>{{ $r['nd_hr'] }}</td>
                <td>₱{{ $r['holiday_pay'] }}</td>
                <td>₱{{ $r['late'] }}</td>
                <td>₱{{ $r['loan'] }}</td>
                <td>₱{{ $r['govt'] }}</td>
                <td>₱{{ $r['net'] }}</td>
              </tr>
            @endforeach

            {{-- TOTALS (16–end) --}}
            <tr class="table-secondary fw-semibold">
              <td>Total</td>
              <td>{{ $secondTotals['worked_hr'] }}</td>
              <td>₱{{ number_format($secondTotals['gross'], 2) }}</td>
              <td>₱{{ number_format($secondTotals['ot_pay'], 2) }}</td>
              <td>{{ $secondTotals['ot_hr'] }}</td>
              <td>₱{{ number_format($secondTotals['nd_pay'], 2) }}</td>
              <td>{{ $secondTotals['nd_hr'] }}</td>
              <td>₱{{ number_format($secondTotals['holiday_pay'], 2) }}</td>
              <td>₱{{ number_format($secondTotals['late'], 2) }}</td>
              <td>₱{{ number_format($secondTotals['loan'], 2) }}</td>
              <td>₱{{ number_format($secondTotals['govt'], 2) }}</td>
              <td>₱{{ number_format($secondTotals['net'], 2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/colresizable@1.6.0/colResizable-1.6.min.js"></script>
  <script>
    $(function(){
      $('#first-cutoff, #second-cutoff').colResizable({
        liveDrag: true,
        gripInnerHtml: "<div class='grip'></div>",
        draggingClass: "dragging"
      });
    });
  </script>
@endpush
