<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payslip #{{ $payslip->id }}</title>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th,td { border: 1px solid #ccc; padding: 4px; }
    th { background: #f0f0f0; }
    .text-right { text-align: right; }
  </style>
</head>
<body>
  <h2>Payroll Summary</h2>
  <p>
    Employee: {{ auth()->user()->name }}<br>
    Period: {{ $payslip->period_start->format('M j, Y') }}
            – {{ $payslip->period_end->format('M j, Y') }}
  </p>

  <table>
    <thead>
      <tr>
        <th>Description</th>
        <th class="text-right">Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Worked Hours</td>
        <td class="text-right">{{ number_format($worked_hours,2) }}</td>
      </tr>
      <tr>
        <td>Overtime Hours</td>
        <td class="text-right">{{ number_format($ot_hours,2) }}</td>
      </tr>
      <tr>
        <td>Overtime Pay</td>
        <td class="text-right">{{ number_format($ot_pay,2) }}</td>
      </tr>
      <tr>
        <td>Deductions</td>
        <td class="text-right">-{{ number_format($deductions,2) }}</td>
      </tr>
      <tr>
        <th>Gross Pay</th>
        <th class="text-right">{{ number_format($gross,2) }}</th>
      </tr>
      <tr>
        <th>Net Pay</th>
        <th class="text-right">{{ number_format($net,2) }}</th>
      </tr>
    </tbody>
  </table>

  <p style="position: fixed; bottom: 1rem; font-size: 10px; width: 100%; text-align: center;">
    Generated {{ now()->toDateTimeString() }}
  </p>
</body>
</html>
