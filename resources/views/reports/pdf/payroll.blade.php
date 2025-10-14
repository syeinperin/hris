<style>
  /* Exact slip size (controller already sets custom paper size) */
  @page { margin: 0.35in 0.30in; }
  body   { font-family: DejaVu Sans, Arial, sans-serif; color:#222; font-size:11px; }
  .card  { border:1px solid #dedede; border-radius:5px; padding:16px 16px 10px; }

  /* Header */
  .brand { display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:8px; }
  .brand-left { display:flex; gap:10px; align-items:flex-start; }
  .logo  { width:48px; height:48px; object-fit:contain; margin-top:2px; }
  .title { line-height:1.15; }
  .company { font-weight:700; letter-spacing:.2px; }
  .muted { color:#666; font-size:10px; }
  .meta  { text-align:right; }
  .meta h2 { margin:0 0 4px; font-size:14px; letter-spacing:.6px; }
  .kv    { margin-top:6px; line-height:1.35; }

  /* Table */
  table { width:100%; border-collapse:collapse; }
  th,td { border:1px solid #e5e5e5; padding:6px 8px; }
  thead th { background:#f6f6f6; font-weight:700; }
  .col-desc{ width:48%; }
  .col-hrs { width:14%; text-align:right; }
  .col-rate{ width:18%; text-align:right; }
  .col-amt { width:20%; text-align:right; }
  .right   { text-align:right; }
  .sec td  { background:#fafafa; font-weight:700; }
  .sum td  { background:#fff; font-weight:700; }
  .net td  { background:#eef6ff; font-weight:800; }

  /* Footer */
  .foot { margin-top:8px; text-align:center; color:#777; font-size:9px; }
</style>

<div class="card">
  <div class="brand">
    <div class="brand-left">
      <img class="logo" src="{{ public_path('images/asiatex-logo.png') }}" alt="ASIATEX">
      <div class="title">
        <div class="company">ASIATEX</div>
        <div class="muted">Asia Textile Manufacturing Corporation</div>
        <div class="kv">
          <strong>Employee:</strong> {{ $employee->name }}<br>
          <strong>Code:</strong> {{ $employee->employee_code }}<br>
          <strong>Department:</strong> {{ optional($employee->department)->name ?? '—' }}<br>
          <strong>Position:</strong> {{ optional($employee->designation)->name ?? '—' }}<br>
          <strong>Rate/hr:</strong> &#8369;{{ number_format($rate, 2) }}
        </div>
      </div>
    </div>
    <div class="meta">
      <h2>PAYSLIP</h2>
      <div class="muted">
        Period: {{ $period_start->format('M d, Y') }} – {{ $period_end->format('M d, Y') }}
      </div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th class="col-desc">Description</th>
        <th class="col-hrs">Hours</th>
        <th class="col-rate">Rate</th>
        <th class="col-amt">Amount</th>
      </tr>
    </thead>
    <tbody>
      <tr class="sec"><td colspan="4">[A] EARNINGS</td></tr>
      <tr>
        <td>Regular Hours</td>
        <td class="col-hrs">{{ number_format($worked_hours, 2) }}</td>
        <td class="col-rate">&#8369;{{ number_format($rate, 2) }}</td>
        <td class="col-amt">&#8369;{{ number_format($base_pay, 2) }}</td>
      </tr>
      <tr>
        <td>Overtime</td>
        <td class="col-hrs">{{ number_format($ot_hours, 2) }}</td>
        <td class="col-rate">&#8369;{{ number_format($rate * 1.25, 2) }}</td>
        <td class="col-amt">&#8369;{{ number_format($ot_pay, 2) }}</td>
      </tr>
      <tr>
        <td>Night Differential</td>
        <td class="col-hrs">{{ number_format($nd_hours, 2) }}</td>
        <td class="col-rate">&#8369;{{ number_format($rate * 0.10, 2) }}</td>
        <td class="col-amt">&#8369;{{ number_format($nd_pay, 2) }}</td>
      </tr>
      <tr class="sum">
        <td>TOTAL EARNINGS</td><td></td><td></td>
        <td class="col-amt">&#8369;{{ number_format($gross, 2) }}</td>
      </tr>

      <tr class="sec"><td colspan="4">[B] OTHER INCOME</td></tr>
      <tr>
        <td>None</td><td></td><td></td>
        <td class="col-amt">&#8369;0.00</td>
      </tr>

      <tr class="sec"><td colspan="4">[C] OTHER DEDUCTIONS</td></tr>
      <tr>
        <td>Loan</td><td></td><td></td>
        <td class="col-amt">-&#8369;{{ number_format($loan, 2) }}</td>
      </tr>

      <tr class="sec"><td colspan="4">[D] GOVERNMENT PREMIUMS &amp; LOANS</td></tr>
      <tr><td>SSS Premium</td><td></td><td></td><td class="col-amt">-&#8369;{{ number_format($sss, 2) }}</td></tr>
      <tr><td>PhilHealth Premium</td><td></td><td></td><td class="col-amt">-&#8369;{{ number_format($phil, 2) }}</td></tr>
      <tr><td>Pag-IBIG Premium</td><td></td><td></td><td class="col-amt">-&#8369;{{ number_format($pag, 2) }}</td></tr>

      <tr class="sum">
        <td>Total Premiums</td><td></td><td></td>
        <td class="col-amt">-&#8369;{{ number_format($sss + $phil + $pag, 2) }}</td>
      </tr>
      <tr class="sum">
        <td>TOTAL AMOUNT (less deductions)</td><td></td><td></td>
        <td class="col-amt">&#8369;{{ number_format($gross - ($loan + $sss + $phil + $pag), 2) }}</td>
      </tr>
      <tr class="net">
        <td>&gt;&gt;&gt; NET PAY</td><td></td><td></td>
        <td class="col-amt">&#8369;{{ number_format($net, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <div class="foot">
    Generated {{ now()->format('Y-m-d H:i') }} • Payslip Ref: —
  </div>
</div>
