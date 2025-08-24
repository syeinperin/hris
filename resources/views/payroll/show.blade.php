@extends('layouts.app')

@section('page_title', 'Payslip')

@push('styles')
<style>
  .nav.brand-pills .nav-link { border:1px solid #e9ecef; color:var(--brand); font-weight:600; }
  .nav.brand-pills .nav-link:not(.active):hover { background: rgba(44,44,84,.06); }
  .nav.brand-pills .nav-link.active { background: var(--brand); color:#fff; }
  .payslip-title { font-weight:700; letter-spacing:.2px; }
  .payslip-subtle { color:#6c757d; }
  .table-scroll .table { margin-bottom:0; }
</style>
@endpush

@section('content')
<div class="container-fluid">
@php
  $first  = $firstRows  ?? $cutoff1  ?? $cut1  ?? $rows1  ?? [];
  $second = $secondRows ?? $cutoff2  ?? $cut2  ?? $rows2  ?? [];
  $month  = request('month', now()->format('Y-m'));
  $empName = $employee->name ?? $employee->employee_name ?? null;

  $pick = function(array $row, array $keys, $fallback = 0.0) {
    foreach ($keys as $k) {
      if (array_key_exists($k, $row) && $row[$k] !== '' && $row[$k] !== null) {
        $v = $row[$k];
        return is_numeric($v) ? (float)$v : (float)preg_replace('/[^\d\.-]/', '', (string)$v);
      }
    }
    return (float)$fallback;
  };

  $sumSet = function($rows) use ($pick) {
    $sum = [
      'worked' => 0.0, 'ot_pay' => 0.0, 'ot' => 0.0,
      'nd_pay' => 0.0, 'nd' => 0.0, 'holiday_pay' => 0.0,
      'late' => 0.0, 'loan' => 0.0, 'govt' => 0.0,
      'gross' => 0.0, 'net' => 0.0,
    ];
    foreach ((array)$rows as $r) {
      $r = is_array($r) ? $r : (array)$r;
      $sum['worked']      += $pick($r, ['worked_hours','worked_hr','worked'], 0);
      $sum['ot_pay']      += $pick($r, ['ot_pay'], 0);
      $sum['ot']          += $pick($r, ['ot_hours','ot_hr','ot'], 0);
      $sum['nd_pay']      += $pick($r, ['nd_pay'], 0);
      $sum['nd']          += $pick($r, ['nd_hours','nd_hr','nd'], 0);
      $sum['holiday_pay'] += $pick($r, ['holiday_pay'], 0);
      $sum['late']        += $pick($r, ['late_ded','late','late_deduction'], 0);
      $sum['loan']        += $pick($r, ['personal_loan','loan','personal_loan_ded'], 0);
      $sum['govt']        += $pick($r, ['govt_ded','govt','government_deduction'], 0);
      $sum['gross']       += $pick($r, ['gross','gross_amount'], 0);
      $sum['net']         += $pick($r, ['net','net_amount'], 0);
    }
    return $sum;
  };

  $sumFirst  = $sumSet($first);
  $sumSecond = $sumSet($second);
@endphp

  <div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-wallet2"></i>
        <h4 class="payslip-title mb-0">
          Payslip @if($empName) <span class="fw-normal">for</span> {{ $empName }} @endif
        </h4>
      </div>
      <div class="d-flex align-items-center gap-3">
        <small class="payslip-subtle">{{ $month }}</small>
        <a href="{{ route('payroll.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Back to Payroll Summary
        </a>
      </div>
    </div>

    <div class="card-body">
      <ul class="nav nav-pills brand-pills mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cut1" type="button">First Cut-off (1–15)</button></li>
        <li class="nav-item ms-2"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cut2" type="button">Second Cut-off (16–31)</button></li>
      </ul>

      <div class="tab-content">
        {{-- Cut-off 1 --}}
        <div class="tab-pane fade show active" id="cut1">
          <div class="table-responsive table-scroll">
            <table class="table table-bordered align-middle table-sticky">
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
                  <th>Personal Loan</th>
                  <th>Govt Ded</th>
                  <th>Gross</th>
                  <th>Net</th>
                </tr>
              </thead>
              <tbody>
                @forelse($first as $r)
                  @php $row = is_array($r) ? $r : (array)$r; @endphp
                  <tr>
                    <td>{{ $row['date'] ?? $row['day'] ?? '' }}</td>
                    <td>{{ (float)($row['worked_hours'] ?? $row['worked_hr'] ?? $row['worked'] ?? 0) }}</td>
                    <td>₱{{ number_format((float)($row['ot_pay'] ?? 0), 2) }}</td>
                    <td>{{ (float)($row['ot_hours'] ?? $row['ot_hr'] ?? $row['ot'] ?? 0) }}</td>
                    <td>₱{{ number_format((float)($row['nd_pay'] ?? 0), 2) }}</td>
                    <td>{{ (float)($row['nd_hours'] ?? $row['nd_hr'] ?? $row['nd'] ?? 0) }}</td>
                    <td>₱{{ number_format((float)($row['holiday_pay'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['late_ded'] ?? $row['late'] ?? $row['late_deduction'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['personal_loan'] ?? $row['loan'] ?? $row['personal_loan_ded'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['govt_ded'] ?? $row['govt'] ?? $row['government_deduction'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['gross'] ?? $row['gross_amount'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['net'] ?? $row['net_amount'] ?? 0), 2) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="12" class="text-center text-muted py-4">No records for 1–15.</td></tr>
                @endforelse
              </tbody>

              {{-- Always render totals if there are rows, even if all zero --}}
              @if(count($first))
              <tfoot>
                <tr class="fw-semibold">
                  <td class="table-secondary">Total</td>
                  <td class="table-secondary">{{ $sumFirst['worked'] }}</td>
                  <td class="table-secondary">₱{{ number_format($sumFirst['ot_pay'], 2) }}</td>
                  <td class="table-secondary">{{ $sumFirst['ot'] }}</td>
                  <td class="table-secondary">₱{{ number_format($sumFirst['nd_pay'], 2) }}</td>
                  <td class="table-secondary">{{ $sumFirst['nd'] }}</td>
                  <td class="table-secondary">₱{{ number_format($sumFirst['holiday_pay'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumFirst['late'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumFirst['loan'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumFirst['govt'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumFirst['gross'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumFirst['net'], 2) }}</td>
                </tr>
              </tfoot>
              @endif
            </table>
          </div>
        </div>

        {{-- Cut-off 2 --}}
        <div class="tab-pane fade" id="cut2">
          <div class="table-responsive table-scroll">
            <table class="table table-bordered align-middle table-sticky">
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
                  <th>Personal Loan</th>
                  <th>Govt Ded</th>
                  <th>Gross</th>
                  <th>Net</th>
                </tr>
              </thead>
              <tbody>
                @forelse($second as $r)
                  @php $row = is_array($r) ? $r : (array)$r; @endphp
                  <tr>
                    <td>{{ $row['date'] ?? $row['day'] ?? '' }}</td>
                    <td>{{ (float)($row['worked_hours'] ?? $row['worked_hr'] ?? $row['worked'] ?? 0) }}</td>
                    <td>₱{{ number_format((float)($row['ot_pay'] ?? 0), 2) }}</td>
                    <td>{{ (float)($row['ot_hours'] ?? $row['ot_hr'] ?? $row['ot'] ?? 0) }}</td>
                    <td>₱{{ number_format((float)($row['nd_pay'] ?? 0), 2) }}</td>
                    <td>{{ (float)($row['nd_hours'] ?? $row['nd_hr'] ?? $row['nd'] ?? 0) }}</td>
                    <td>₱{{ number_format((float)($row['holiday_pay'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['late_ded'] ?? $row['late'] ?? $row['late_deduction'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['personal_loan'] ?? $row['loan'] ?? $row['personal_loan_ded'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['govt_ded'] ?? $row['govt'] ?? $row['government_deduction'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['gross'] ?? $row['gross_amount'] ?? 0), 2) }}</td>
                    <td>₱{{ number_format((float)($row['net'] ?? $row['net_amount'] ?? 0), 2) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="12" class="text-center text-muted py-4">No records for 16–31.</td></tr>
                @endforelse
              </tbody>

              @if(count($second))
              <tfoot>
                <tr class="fw-semibold">
                  <td class="table-secondary">Total</td>
                  <td class="table-secondary">{{ $sumSecond['worked'] }}</td>
                  <td class="table-secondary">₱{{ number_format($sumSecond['ot_pay'], 2) }}</td>
                  <td class="table-secondary">{{ $sumSecond['ot'] }}</td>
                  <td class="table-secondary">₱{{ number_format($sumSecond['nd_pay'], 2) }}</td>
                  <td class="table-secondary">{{ $sumSecond['nd'] }}</td>
                  <td class="table-secondary">₱{{ number_format($sumSecond['holiday_pay'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumSecond['late'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumSecond['loan'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumSecond['govt'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumSecond['gross'], 2) }}</td>
                  <td class="table-secondary">₱{{ number_format($sumSecond['net'], 2) }}</td>
                </tr>
              </tfoot>
              @endif
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
