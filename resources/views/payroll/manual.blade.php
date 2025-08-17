@extends('layouts.app')

@section('page_title','Manual Payroll')

@push('styles')
<style>
  /* Kill horizontal page scrollbars just in case */
  html, body { overflow-x: hidden; }

  /* Wrapper avoids inner scrollbars on the table */
  .manual-table-wrapper { overflow-x: hidden; }

  /* Compact table that stays inside the card width */
  #manualTable.manual-table {
    table-layout: fixed;   /* obey colgroup widths */
    width: 100%;
  }
  #manualTable.manual-table thead th {
    white-space: normal;   /* allow wrapping, fixes overflow */
    font-size: .85rem;
    line-height: 1.1;
  }
  #manualTable.manual-table td {
    vertical-align: middle;
  }

  /* Inputs/selects a bit tighter so they don’t bloat cells */
  #manualTable .form-control-sm,
  #manualTable .form-select-sm {
    padding: .25rem .5rem;
  }

  /* Right align numeric displays */
  .num { text-align: right; }

  /* Keep the totals card simple */
  .totals-card { background:#fff; }

  /* Slightly tighter padding on very narrow screens */
  @media (max-width: 1400px) {
    .card .card-body { padding: 1rem; }
  }
</style>
@endpush

@section('content')
<div class="container-fluid">
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Manual Payroll</h4>
      <a href="{{ route('payroll.index') }}" class="btn btn-secondary">← Back to Payroll Summary</a>
    </div>

    <div class="card-body">
      {{-- Select Employee + Period --}}
      <form id="manualForm" method="POST" action="{{ route('payroll.manual.store') }}">
        @csrf
        <input type="hidden" name="rows_json" id="rows_json">
        <input type="hidden" name="rate_per_hour" id="rate_per_hour" value="0">

        <div class="row g-3 align-items-end mb-3">
          <div class="col-md-4">
            <label class="form-label">Employee</label>
            <select name="employee_id" id="employee_id" class="form-select" required>
              <option value="" selected disabled>— choose employee —</option>
              @foreach($employees as $e)
                <option value="{{ $e->id }}">{{ $e->employee_code }} — {{ $e->name }}</option>
              @endforeach
            </select>
            <div class="small text-muted mt-2" id="empMeta">Rate/hr: ₱<span id="rateLabel">0.00</span></div>
          </div>
          <div class="col-md-3">
            <label class="form-label">Period Start</label>
            <input type="date" class="form-control" name="period_start" value="{{ $periodStart }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Period End</label>
            <input type="date" class="form-control" name="period_end" value="{{ $periodEnd }}" required>
          </div>
          <div class="col-md-2 d-grid">
            <button type="button" id="addRowBtn" class="btn btn-outline-primary">
              Add Row
            </button>
          </div>
        </div>

        {{-- Editable table (similar columns to payroll.show) --}}
        <div class="manual-table-wrapper">
          <table id="manualTable" class="table table-bordered align-middle manual-table">
            {{-- Make widths predictable and inside 100% --}}
            <colgroup>
              <col style="width:8%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:9%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:6%">
              <col style="width:3%">
            </colgroup>
            <thead class="table-light">
              <tr>
                <th>Date</th>
                <th class="text-center">Worked<br>(hr)</th>

                <th class="text-center">OT<br>(hr)</th>
                <th class="text-center">OT<br>Formula</th>
                <th class="text-center">OT<br>Pay</th>

                <th class="text-center">ND<br>(hr)</th>
                <th class="text-center">ND<br>Pay</th>

                <th class="text-center">Holiday</th>
                <th class="text-center">Late</th>
                <th class="text-center">Loan</th>
                <th class="text-center">Govt</th>
                <th class="text-center">Gross</th>
                <th class="text-center">Net</th>
                <th></th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        {{-- Totals --}}
        <div class="row mt-3">
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <div class="card totals-card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>Worked Hours</div>
                  <div><strong id="tWorked">0</strong></div>
                </div>
                <div class="d-flex justify-content-between">
                  <div>OT Hours</div>
                  <div><strong id="tOT_Hrs">0</strong></div>
                </div>
                <div class="d-flex justify-content-between">
                  <div>OT Pay</div>
                  <div><strong>₱<span id="tOT_Pay">0.00</span></strong></div>
                </div>
                <div class="d-flex justify-content-between">
                  <div>All Deductions</div>
                  <div><strong>₱<span id="tDed">0.00</span></strong></div>
                </div>
                <div class="d-flex justify-content-between">
                  <div>Gross</div>
                  <div><strong>₱<span id="tGross">0.00</span></strong></div>
                </div>
                <div class="d-flex justify-content-between fs-5 mt-2">
                  <div>Net</div>
                  <div><strong>₱<span id="tNet">0.00</span></strong></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
          <button type="submit" class="btn btn-primary">Save Manual Payslip</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const $emp   = document.getElementById('employee_id');
  const $rateH = document.getElementById('rate_per_hour');
  const rateLabel = document.getElementById('rateLabel');
  const tbody = document.querySelector('#manualTable tbody');

  // OT options (must match server map)
  const OT_OPTIONS = [
    { key: 'OT_REG',     label: 'Regular (1.25x)', mult: 1.25 },
    { key: 'OT_REST',    label: 'Rest Day (1.30x)', mult: 1.30 },
    { key: 'OT_HOLIDAY', label: 'Holiday (2.60x)', mult: 2.60 },
  ];
  const ND_RATE = 0.10;

  function currency(v){ return (Math.round(v * 100) / 100).toFixed(2); }

  function addRow(dateStr=''){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="date" class="form-control form-control-sm" value="${dateStr}"></td>
      <td><input type="number" step="0.01" min="0" class="form-control form-control-sm w-100 text-end worked"></td>

      <td><input type="number" step="0.01" min="0" class="form-control form-control-sm w-100 text-end ot_hr"></td>
      <td>
        <select class="form-select form-select-sm ot_key">
          ${OT_OPTIONS.map(o => `<option value="${o.key}">${o.label}</option>`).join('')}
        </select>
      </td>
      <td class="num"><span class="ot_pay">0.00</span></td>

      <td><input type="number" step="0.01" min="0" class="form-control form-control-sm w-100 text-end nd_hr"></td>
      <td class="num"><span class="nd_pay">0.00</span></td>

      <td><input type="number" step="0.01" min="0" class="form-control form-control-sm w-100 text-end holiday"></td>
      <td><input type="number" step="0.01" min="0" class="form-control form-control-sm w-100 text-end late"></td>
      <td><input type="number" step="0.01" min="0" class="form-control form-control-sm w-100 text-end loan"></td>
      <td><input type="number" step="0.01" min="0" class="form-control form-control-sm w-100 text-end govt"></td>

      <td class="num"><span class="gross">0.00</span></td>
      <td class="num"><span class="net">0.00</span></td>
      <td><button type="button" class="btn btn-sm btn-outline-danger del">×</button></td>
    `;
    tbody.appendChild(tr);
    recalcRow(tr);
    bindRow(tr);
    recalcTotals();
  }

  function bindRow(tr){
    tr.querySelectorAll('input,select').forEach(el => {
      el.addEventListener('input', () => { recalcRow(tr); recalcTotals(); });
    });
    tr.querySelector('.del').addEventListener('click', () => {
      tr.remove();
      recalcTotals();
    });
  }

  function getRate(){ return parseFloat($rateH.value || '0') || 0; }

  function recalcRow(tr){
    const rate = getRate();
    const worked = parseFloat(tr.querySelector('.worked')?.value || '0') || 0;
    const otHr   = parseFloat(tr.querySelector('.ot_hr')?.value || '0') || 0;
    const otKey  = tr.querySelector('.ot_key')?.value || 'OT_REG';
    const otMult = (OT_OPTIONS.find(o => o.key === otKey)?.mult) || 1.25;
    const ndHr   = parseFloat(tr.querySelector('.nd_hr')?.value || '0') || 0;

    const holiday= parseFloat(tr.querySelector('.holiday')?.value || '0') || 0;
    const late   = parseFloat(tr.querySelector('.late')?.value || '0') || 0;
    const loan   = parseFloat(tr.querySelector('.loan')?.value || '0') || 0;
    const govt   = parseFloat(tr.querySelector('.govt')?.value || '0') || 0;

    const basePay = worked * rate;
    const otPay   = otHr * rate * otMult;
    const ndPay   = ndHr * rate * ND_RATE;

    tr.querySelector('.ot_pay').textContent = currency(otPay);
    tr.querySelector('.nd_pay').textContent = currency(ndPay);

    const gross = basePay + otPay + ndPay + holiday;
    const ded   = late + loan + govt;
    const net   = gross - ded;

    tr.querySelector('.gross').textContent = currency(gross);
    tr.querySelector('.net').textContent   = currency(net);
  }

  function recalcTotals(){
    let tWorked=0, tOT_Hrs=0, tOT_Pay=0, tDed=0, tGross=0, tNet=0;
    const rate = getRate();

    tbody.querySelectorAll('tr').forEach(tr => {
      const worked = parseFloat(tr.querySelector('.worked')?.value || '0') || 0;
      const otHr   = parseFloat(tr.querySelector('.ot_hr')?.value || '0') || 0;
      const otKey  = tr.querySelector('.ot_key')?.value || 'OT_REG';
      const otMult = (OT_OPTIONS.find(o => o.key === otKey)?.mult) || 1.25;
      const ndHr   = parseFloat(tr.querySelector('.nd_hr')?.value || '0') || 0;
      const holiday= parseFloat(tr.querySelector('.holiday')?.value || '0') || 0;
      const late   = parseFloat(tr.querySelector('.late')?.value || '0') || 0;
      const loan   = parseFloat(tr.querySelector('.loan')?.value || '0') || 0;
      const govt   = parseFloat(tr.querySelector('.govt')?.value || '0') || 0;

      const basePay = worked * rate;
      const otPay   = otHr * rate * otMult;
      const ndPay   = ndHr * rate * ND_RATE;
      const gross   = basePay + otPay + ndPay + holiday;
      const ded     = late + loan + govt;
      const net     = gross - ded;

      tWorked += worked;
      tOT_Hrs += otHr;
      tOT_Pay += otPay;
      tDed    += ded;
      tGross  += gross;
      tNet    += net;
    });

    document.getElementById('tWorked').textContent = tWorked.toFixed(2);
    document.getElementById('tOT_Hrs').textContent = tOT_Hrs.toFixed(2);
    document.getElementById('tOT_Pay').textContent = currency(tOT_Pay);
    document.getElementById('tDed').textContent    = currency(tDed);
    document.getElementById('tGross').textContent  = currency(tGross);
    document.getElementById('tNet').textContent    = currency(tNet);
  }

  // Load rate when employee changes
  $emp.addEventListener('change', async function(){
    const id = this.value;
    if (!id) return;
    const res = await fetch(`{{ route('payroll.manual.employee') }}?id=${id}`);
    if (!res.ok) { alert('Cannot load employee rate.'); return; }
    const data = await res.json();
    const rate = parseFloat(data.rate_per_hour || 0) || 0;
    $rateH.value = rate.toString();
    rateLabel.textContent = rate.toFixed(2);
    recalcTotals();
  });

  // Add row button
  document.getElementById('addRowBtn').addEventListener('click', () => addRow());

  // Serialize rows before submit
  document.getElementById('manualForm').addEventListener('submit', () => {
    const rows = [];
    tbody.querySelectorAll('tr').forEach(tr => {
      rows.push({
        date:        tr.querySelector('input[type="date"]')?.value || null,
        worked_hr:   tr.querySelector('.worked')?.value || '0',
        ot_hr:       tr.querySelector('.ot_hr')?.value || '0',
        ot_key:      tr.querySelector('.ot_key')?.value || 'OT_REG',
        nd_hr:       tr.querySelector('.nd_hr')?.value || '0',
        holiday_pay: tr.querySelector('.holiday')?.value || '0',
        late:        tr.querySelector('.late')?.value || '0',
        loan:        tr.querySelector('.loan')?.value || '0',
        govt:        tr.querySelector('.govt')?.value || '0',
      });
    });
    document.getElementById('rows_json').value = JSON.stringify(rows);
  });

  // Start with one row
  addRow();
})();
</script>
@endpush
