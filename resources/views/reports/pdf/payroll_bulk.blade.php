<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payslips (Bulk)</title>
  <style>
    @page { margin: 18pt; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 10pt; color:#222; }
    .card { border:1px solid #ddd; border-radius:6pt; padding:10pt 12pt; }
    .brand { display:flex; justify-content:space-between; gap:12pt; align-items:flex-start; margin-bottom:8pt; }
    .logo { width:60pt; height:auto; }
    .h1 { font-weight:700; font-size:12pt; letter-spacing:.5pt }
    .muted { color:#666; font-size:9pt; }
    h2 { margin:0; font-size:11pt; letter-spacing:.3pt; }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #e5e5e5; padding:6pt 6pt; }
    thead th { background:#f7f7f7; font-weight:600; font-size:9pt; }
    .right { text-align:right; }
    .sec td { background:#fafafa; font-weight:600; }
    .bold td { font-weight:700; }
    .foot { text-align:center; font-size:8pt; color:#666; margin-top:8pt; }
    .page-break { page-break-after: always; }
  </style>
</head>
<body>

@foreach($items as $i => $x)
  @php
    $employee     = $x['employee'];
    $period_start = $x['period_start'];
    $period_end   = $x['period_end'];
    $rate         = $x['rate'];
    $worked_hours = $x['worked_hours'];
    $ot_hours     = $x['ot_hours'];
    $nd_hours     = $x['nd_hours'];
    $base_pay     = $x['base_pay'];
    $ot_pay       = $x['ot_pay'];
    $nd_pay       = $x['nd_pay'];
    $gross        = $x['gross'];
    $loan         = $x['loan'];
    $sss          = $x['sss'];
    $phil         = $x['phil'];
    $pag          = $x['pag'];
    $deductions   = $x['deductions'];
    $net          = $x['net'];
  @endphp

  <div class="card">
    <div class="brand">
      <div>
        <img class="logo" src="{{ public_path('images/asiatex-logo.png') }}" alt="ASIATEX">
        <div class="h1">ASIATEX</div>
        <div class="muted">Asia Textile Manufacturing Corporation</div>
        <div class="muted" style="margin-top:6px; line-height:1.35;">
          <strong>Employee:</strong> {{ $employee->name }}<br>
          <strong>Code:</strong> {{ $employee->employee_code }}<br>
          <strong>Department:</strong> {{ optional($employee->department)->name ?? '—' }}<br>
          <strong>Position:</strong> {{ optional($employee->designation)->name ?? '—' }}<br>
          <strong>Rate/hr:</strong> ₱{{ number_format($rate,2) }}
        </div>
      </div>
      <div class="meta">
        <h2>PAYSLIP</h2>
        Period: {{ $period_start->format('M d, Y') }} – {{ $period_end->format('M d, Y') }}
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th>Description</th><th class="right">Hours</th><th class="right">Rate</th><th class="right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr class="sec"><td colspan="4">[A] EARNINGS</td></tr>
        <tr><td>Regular Hours</td><td class="right">{{ number_format($worked_hours,2) }}</td><td class="right">₱{{ number_format($rate,2) }}</td><td class="right">₱{{ number_format($base_pay,2) }}</td></tr>
        <tr><td>Overtime</td><td class="right">{{ number_format($ot_hours,2) }}</td><td class="right">₱{{ number_format($rate*1.25,2) }}</td><td class="right">₱{{ number_format($ot_pay,2) }}</td></tr>
        <tr><td>Night Differential</td><td class="right">{{ number_format($nd_hours,2) }}</td><td class="right">₱{{ number_format($rate*0.10,2) }}</td><td class="right">₱{{ number_format($nd_pay,2) }}</td></tr>
        <tr class="bold"><td>TOTAL EARNINGS</td><td></td><td></td><td class="right">₱{{ number_format($gross,2) }}</td></tr>

        <tr class="sec"><td colspan="4">[B] OTHER INCOME</td></tr>
        <tr><td colspan="3">None</td><td class="right">₱0.00</td></tr>

        <tr class="sec"><td colspan="4">[C] OTHER DEDUCTIONS</td></tr>
        <tr><td>Loan</td><td></td><td></td><td class="right">-₱{{ number_format($loan,2) }}</td></tr>

        <tr class="sec"><td colspan="4">[D] GOVERNMENT PREMIUMS & LOANS</td></tr>
        <tr><td>SSS Premium</td><td></td><td></td><td class="right">-₱{{ number_format($sss,2) }}</td></tr>
        <tr><td>PhilHealth Premium</td><td></td><td></td><td class="right">-₱{{ number_format($phil,2) }}</td></tr>
        <tr><td>Pag-IBIG Premium</td><td></td><td></td><td class="right">-₱{{ number_format($pag,2) }}</td></tr>

        <tr class="bold"><td>Total Premiums</td><td></td><td></td><td class="right">-₱{{ number_format($sss+$phil+$pag,2) }}</td></tr>
        <tr class="bold"><td>TOTAL AMOUNT (less deductions)</td><td></td><td></td><td class="right">₱{{ number_format($gross-($loan+$sss+$phil+$pag),2) }}</td></tr>
        <tr class="bold"><td>>> NET PAY</td><td></td><td></td><td class="right">₱{{ number_format($net,2) }}</td></tr>
      </tbody>
    </table>

    <div class="foot">
      Generated {{ now()->format('Y-m-d H:i') }} • Payslip Ref: —
    </div>
  </div>

  @if($i < count($items)-1)
    <div class="page-break"></div>
  @endif
@endforeach

</body>
</html>
