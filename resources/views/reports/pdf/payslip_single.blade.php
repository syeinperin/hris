<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payslip — {{ $employee->employee_code }}</title>
  <style>
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color:#222; }
    h2,h3 { margin: 0 0 10px; }
    .meta { margin: 0 0 14px; }
    table { width: 100%; border-collapse: collapse; }
    th,td { border: 1px solid #ddd; padding: 6px 8px; }
    thead th { background: #f2f2f2; }
    .right { text-align: right; }
    .bold { font-weight: 700; }
    .muted { color:#666; }
    .split { width:100%; display:flex; gap:16px; }
    .col  { flex:1; }
    .mt-10{ margin-top:10px; }
  </style>
</head>
<body>
  <h2>Payslip</h2>

  <p class="meta">
    Employee: <strong>{{ $employee->name }}</strong> ({{ $employee->employee_code }})<br>
    Position: {{ optional($employee->designation)->name ?? '—' }}<br>
    Period:
    {{ \Carbon\Carbon::parse($from)->format('M j, Y') }}
    –
    {{ \Carbon\Carbon::parse($to)->format('M j, Y') }}
  </p>

  <div class="split">
    {{-- Earnings --}}
    <div class="col">
      <h3>Earnings</h3>
      <table>
        <tbody>
          <tr>
            <td>Base Rate</td>
            <td class="right">₱{{ number_format((float)$rate_hr, 2) }}/hr</td>
          </tr>
          <tr>
            <td>Worked Hours</td>
            <td class="right">{{ number_format((float)$worked_hours, 2) }}</td>
          </tr>
          <tr>
            <td>Base Pay</td>
            <td class="right">₱{{ number_format((float)$base_pay, 2) }}</td>
          </tr>
          <tr>
            <td>Overtime Hours</td>
            <td class="right">{{ number_format((float)$ot_hours, 2) }}</td>
          </tr>
          <tr>
            <td>Overtime Pay</td>
            <td class="right">₱{{ number_format((float)$ot_pay, 2) }}</td>
          </tr>
          <tr>
            <td>Night Differential (hrs)</td>
            <td class="right">{{ number_format((float)$nd_hours, 2) }}</td>
          </tr>
          <tr>
            <td>Night Differential Pay</td>
            <td class="right">₱{{ number_format((float)$nd_pay, 2) }}</td>
          </tr>
          <tr>
            <td>Holiday Pay</td>
            <td class="right">₱{{ number_format((float)$holiday_pay, 2) }}</td>
          </tr>
          <tr class="bold">
            <td>GROSS EARNINGS</td>
            <td class="right">₱{{ number_format((float)$gross, 2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- Deductions --}}
    <div class="col">
      <h3>Deductions</h3>
      <table>
        <tbody>
          <tr>
            <td>Lateness</td>
            <td class="right">-₱{{ number_format((float)$late, 2) }}</td>
          </tr>
          <tr>
            <td>Loan</td>
            <td class="right">-₱{{ number_format((float)$loan, 2) }}</td>
          </tr>
          <tr>
            <td>SSS</td>
            <td class="right">-₱{{ number_format((float)$sss, 2) }}</td>
          </tr>
          <tr>
            <td>PhilHealth</td>
            <td class="right">-₱{{ number_format((float)$phil, 2) }}</td>
          </tr>
          <tr>
            <td>Pag-IBIG</td>
            <td class="right">-₱{{ number_format((float)$pag, 2) }}</td>
          </tr>
          <tr class="bold">
            <td>TOTAL DEDUCTIONS</td>
            <td class="right">-₱{{ number_format((float)$deductions, 2) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Net summary --}}
  <table class="mt-10">
    <tbody>
      <tr class="bold">
        <td style="width:70%;">NET PAY</td>
        <td class="right" style="width:30%;">₱{{ number_format((float)$net, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <p class="muted" style="margin-top:10px;text-align:center;">
    Generated {{ now()->toDateTimeString() }}
  </p>
</body>
</html>
