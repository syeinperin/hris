<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payslip – {{ $employee->name }}</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#222; }
    .header { text-align:center; margin-bottom: 10px; }
    .h1 { font-size: 18px; margin: 0 0 4px 0; }
    .muted { color:#666; }
    .row { display:flex; justify-content:space-between; margin-bottom:8px; }
    .box { border:1px solid #ddd; padding:10px; border-radius:4px; margin-bottom:10px; }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #ddd; padding:6px; text-align:left; }
    th { background:#f5f5f5; }
    .right { text-align:right; }
    .bold { font-weight:700; }
  </style>
</head>
<body>
  <div class="header">
    <div class="h1">PAYSLIP</div>
    <div class="muted">Period: {{ $from }} to {{ $to }}</div>
  </div>

  <div class="box">
    <div class="row">
      <div>
        <strong>Employee:</strong> {{ $employee->name }}<br>
        <strong>Code:</strong> {{ $employee->employee_code }}
      </div>
      <div>
        <strong>Rate / Hour:</strong> ₱{{ number_format($rate_hr,2) }}<br>
      </div>
    </div>
  </div>

  <div class="box">
    <table>
      <tr>
        <th>Description</th>
        <th class="right">Hours</th>
        <th class="right">Rate</th>
        <th class="right">Amount</th>
      </tr>
      <tr>
        <td>Regular</td>
        <td class="right">{{ number_format($worked_hours,2) }}</td>
        <td class="right">₱{{ number_format($rate_hr,2) }}</td>
        <td class="right">₱{{ number_format($base_pay,2) }}</td>
      </tr>
      <tr>
        <td>Overtime</td>
        <td class="right">{{ number_format($ot_hours,2) }}</td>
        <td class="right">₱{{ number_format($rate_hr * 1.25,2) }}</td>
        <td class="right">₱{{ number_format($ot_pay,2) }}</td>
      </tr>
      <tr>
        <td>Night Differential</td>
        <td class="right">{{ number_format($nd_hours,2) }}</td>
        <td class="right">₱{{ number_format($rate_hr * 0.10,2) }}</td>
        <td class="right">₱{{ number_format($nd_pay,2) }}</td>
      </tr>
      <tr>
        <td>Holiday Pay</td>
        <td class="right">—</td>
        <td class="right">—</td>
        <td class="right">₱{{ number_format($holiday_pay,2) }}</td>
      </tr>
      <tr>
        <th colspan="3" class="right">Gross Pay</th>
        <th class="right">₱{{ number_format($gross,2) }}</th>
      </tr>
      <tr>
        <td class="bold" colspan="4">Deductions</td>
      </tr>
      <tr>
        <td>Late</td><td class="right">—</td><td class="right">—</td>
        <td class="right">-₱{{ number_format($late,2) }}</td>
      </tr>
      <tr>
        <td>Loan</td><td class="right">—</td><td class="right">—</td>
        <td class="right">-₱{{ number_format($loan,2) }}</td>
      </tr>
      <tr>
        <td>SSS</td><td class="right">—</td><td class="right">—</td>
        <td class="right">-₱{{ number_format($sss,2) }}</td>
      </tr>
      <tr>
        <td>PhilHealth</td><td class="right">—</td><td class="right">—</td>
        <td class="right">-₱{{ number_format($phil,2) }}</td>
      </tr>
      <tr>
        <td>Pag-IBIG</td><td class="right">—</td><td class="right">—</td>
        <td class="right">-₱{{ number_format($pag,2) }}</td>
      </tr>
      <tr>
        <th colspan="3" class="right">Total Deductions</th>
        <th class="right">-₱{{ number_format($deductions,2) }}</th>
      </tr>
      <tr>
        <th colspan="3" class="right">Net Pay</th>
        <th class="right bold">₱{{ number_format($net,2) }}</th>
      </tr>
    </table>
  </div>

  <p class="muted" style="margin-top:10px;text-align:center;">
    Generated {{ now()->format('Y-m-d H:i') }}
  </p>
</body>
</html>
