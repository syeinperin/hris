{{-- resources/views/loans/form.blade.php --}}
{{-- 
     This partial expects three variables:
       • $loan      — the Loan model (or null)
       • $employees — a collection/array of [id => name]
       • $types     — a collection/array of [id => name]
       • $plans     — a collection/array of [id => name]
--}}

@php
  // Helper to grab an old value, falling back to the loan's attribute if present
  $fv = fn($field, $fallback = '') => old($field, $fallback);
@endphp

<div class="mb-3">
  <label class="form-label">Employee *</label>
  <select name="employee_id" class="form-select">
    <option disabled {{ empty($loan) ? 'selected' : '' }}>— choose employee —</option>
    @foreach($employees as $id => $name)
      <option value="{{ $id }}"
        {{ $fv('employee_id', $loan->employee_id ?? '') == $id ? 'selected':'' }}>
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
      <option value="{{ $id }}"
        {{ $fv('loan_type_id', $loan->loan_type_id ?? '') == $id ? 'selected':'' }}>
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
      <option value="{{ $id }}"
        {{ $fv('plan_id', $loan->plan_id ?? '') == $id ? 'selected':'' }}>
        {{ $name }}
      </option>
    @endforeach
  </select>
</div>

<div class="mb-3">
  <label class="form-label">Principal Amount *</label>
  <input type="number" step="0.01" name="principal"
         class="form-control"
         value="{{ $fv('principal', $loan->principal_amount ?? '') }}">
</div>

<div class="mb-3">
  <label class="form-label">Interest Rate (%) *</label>
  <input type="number" step="0.01" name="interest_rate"
         class="form-control"
         value="{{ $fv('interest_rate', $loan->interest_rate ?? '') }}">
</div>

<div class="mb-3">
  <label class="form-label">Term (months) *</label>
  <input type="number" name="term_months"
         class="form-control"
         value="{{ $fv('term_months', $loan->term_months ?? '') }}">
</div>

<div class="mb-3">
  <label class="form-label">Next Payment Date *</label>
  <input type="date" name="next_payment_date"
         class="form-control"
         value="{{ $fv(
           'next_payment_date',
           // <— wrap the **model** in optional(), not the property!
           optional($loan)->next_payment_date?->toDateString()
         ) }}">
</div>

<div class="mb-3">
  <label class="form-label">Release Date *</label>
  <input type="date" name="released_at"
         class="form-control"
         value="{{ $fv(
           'released_at',
           optional($loan)->released_at?->toDateString()
         ) }}">
</div>
