<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payslip #{{ $payslip->id }}</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#222; }
    h2 { margin: 0 0 10px; }
    .meta { margin: 0 0 16px; }
    table { width: 100%; border-collapse: collapse; }
    th,td { border: 1px solid #ddd; padding: 6px 8px; }
    thead th { background: #f2f2f2; }
    tfoot th { background: #f7f7f7; }
    .muted { color:#666; }
    .right { text-align: right; }
    .bold  { font-weight: 700; }
    .section { margin-top: 14px; }
  </style>
</head>
<body>
  <h2>Payroll Summary</h2>
  <p class="meta">
    Employee: {{ auth()->user()->name }}<br>
    Period: {{ $payslip->period_start->format('M j, Y') }}
            – {{ $payslip->period_end->format('M j, Y') }}
  </p>

  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="right">Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Base Rate</td>
        <td class="right">₱{{ number_format($base_rate, 2) }}/hr</td>
      </tr>
      <tr>
        <td>Worked Hours</td>
        <td class="right">{{ number_format($worked_hours, 2) }}</td>
      </tr>
      <tr>
        <td>Base Pay</td>
        <td class="right">₱{{ number_format($base_pay, 2) }}</td>
      </tr>

      <tr>
        <td>Overtime Hours</td>
        <td class="right">{{ number_format($ot_hours, 2) }}</td>
      </tr>
      <tr>
        <td>Overtime Pay</td>
        <td class="right">₱{{ number_format($ot_pay, 2) }}</td>
      </tr>

      <tr>
        <td>Night Differential Hours</td>
        <td class="right">{{ number_format($nd_hours, 2) }}</td>
      </tr>
      <tr>
        <td>Night Differential Pay</td>
        <td class="right">₱{{ number_format($nd_pay, 2) }}</td>
      </tr>

      <tr>
        <td class="bold">Gross Pay</td>
        <td class="right bold">₱{{ number_format($gross, 2) }}</td>
      </tr>

      {{-- Deductions breakdown --}}
      <tr>
        <td colspan="2" class="bold">Deductions</td>
      </tr>
      <tr>
        <td class="muted">Personal Loan</td>
        <td class="right">-₱{{ number_format($loan, 2) }}</td>
      </tr>
      <tr>
        <td class="muted">SSS</td>
        <td class="right">-₱{{ number_format($sss, 2) }}</td>
      </tr>
      <tr>
        <td class="muted">PhilHealth</td>
        <td class="right">-₱{{ number_format($phil, 2) }}</td>
      </tr>
      <tr>
        <td class="muted">Pag-IBIG</td>
        <td class="right">-₱{{ number_format($pag, 2) }}</td>
      </tr>
      <tr>
        <td class="bold">Total Deductions</td>
        <td class="right bold">-₱{{ number_format($deductions, 2) }}</td>
      </tr>

      <tr>
        <td class="bold">Net Pay</td>
        <td class="right bold">₱{{ number_format($net, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <p class="muted" style="margin-top:12px;text-align:center;">
    Generated {{ now()->toDateTimeString() }}
  </p>
</body>
</html>
