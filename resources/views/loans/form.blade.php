{{-- resources/views/loans/form.blade.php --}}
@php
  $fv = fn($field, $fallback = '') => old($field, $fallback);
@endphp

<div class="mb-3">
  <label class="form-label">Employee *</label>
  <select name="employee_id" class="form-select">
    <option disabled {{ empty($loan) ? 'selected' : '' }}>— choose employee —</option>
    @foreach($employees as $id => $name)
      <option value="{{ $id }}" {{ $fv('employee_id', $loan->employee_id ?? '') == $id ? 'selected':'' }}>
        {{ $name }}
      </option>
    @endforeach
  </select>
</div>

<div class="mb-3">
  <label class="form-label">Loan Type *</label>
  <select name="loan_type_id" class="form-select">
    <option disabled {{ empty($loan) ? 'selected' : '' }}>— choose type —</option>
    @foreach($types as $id => $name)
      <option value="{{ $id }}" {{ $fv('loan_type_id', $loan->loan_type_id ?? '') == $id ? 'selected':'' }}>
        {{ $name }}
      </option>
    @endforeach
  </select>
</div>

<div class="mb-3">
  <label class="form-label">Loan Plan *</label>
  <select name="plan_id" class="form-select">
    <option disabled {{ empty($loan) ? 'selected' : '' }}>— choose plan —</option>
    @foreach($plans as $id => $name)
      <option value="{{ $id }}" {{ $fv('plan_id', $loan->plan_id ?? '') == $id ? 'selected':'' }}>
        {{ $name }}
      </option>
    @endforeach
  </select>
</div>

<div class="mb-3">
  <label class="form-label">Principal Amount *</label>
  <input type="number" step="0.01" name="principal_amount"
         class="form-control"
         value="{{ $fv('principal_amount', $loan->principal_amount ?? '') }}">
</div>

<div class="mb-3">
  <label class="form-label">Interest Rate (%) *</label>
  <input type="number" step="0.01" name="interest_rate"
         class="form-control"
         value="{{ $fv('interest_rate', $loan->interest_rate ?? '') }}">
  <small class="text-muted">If left blank, plan’s default rate will be used.</small>
</div>

<div class="mb-3">
  <label class="form-label">Term (months) *</label>
  <input type="number" name="term_months"
         class="form-control"
         value="{{ $fv('term_months', $loan->term_months ?? '') }}">
  <small class="text-muted">If left blank, plan’s months will be used.</small>
</div>

<div class="mb-3">
  <label class="form-label">Next Payment Date *</label>
  <input type="date" name="next_payment_date" class="form-control"
         value="{{ $fv('next_payment_date', optional($loan)->next_payment_date?->toDateString()) }}">
</div>

<div class="mb-3">
  <label class="form-label">Release Date *</label>
  <input type="date" name="released_at" class="form-control"
         value="{{ $fv('released_at', optional($loan)->released_at?->toDateString()) }}">
</div>

<div class="mb-3">
  <label class="form-label">Status</label>
  <select name="status" class="form-select">
    @foreach(['active'=>'Active','paid'=>'Paid','defaulted'=>'Defaulted'] as $k=>$label)
      <option value="{{ $k }}" {{ $fv('status', $loan->status ?? 'active')===$k ? 'selected':'' }}>{{ $label }}</option>
    @endforeach
  </select>
</div>
