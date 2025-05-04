{{-- resources/views/plans/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Create New Performance Plan</h3>

  <form method="POST" action="{{ route('plans.store') }}">
    @csrf

    {{-- Plan Info --}}
    <div class="row g-3 mb-4">
      <div class="col-md-6 form-floating">
        <input
          type="text"
          name="name"
          id="plan-name"
          class="form-control @error('name') is-invalid @enderror"
          placeholder="Plan Name"
          value="{{ old('name') }}"
          required>
        <label for="plan-name">Plan Name *</label>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-3 form-floating">
        <input
          type="date"
          name="start_date"
          id="plan-start"
          class="form-control @error('start_date') is-invalid @enderror"
          value="{{ old('start_date') }}">
        <label for="plan-start">Start Date</label>
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="col-md-3 form-floating">
        <input
          type="date"
          name="end_date"
          id="plan-end"
          class="form-control @error('end_date') is-invalid @enderror"
          value="{{ old('end_date') }}">
        <label for="plan-end">End Date</label>
        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>

    {{-- KPI Items --}}
    <h5>KPI Items <small class="text-muted">(Metric & Weight %)</small></h5>
    <table class="table table-bordered" id="items-table">
      <thead class="table-light">
        <tr>
          <th style="width:60%">Metric</th>
          <th style="width:20%">Weight (%)</th>
          <th style="width:20%"></th>
        </tr>
      </thead>
      <tbody>
        @if(old('items'))
          @foreach(old('items') as $i => $old)
            <tr>
              <td>
                <input
                  type="text"
                  name="items[{{ $i }}][metric]"
                  class="form-control @error("items.$i.metric") is-invalid @enderror"
                  value="{{ $old['metric'] }}"
                  required>
                @error("items.$i.metric")<div class="invalid-feedback">{{ $message }}</div>@enderror
              </td>
              <td>
                <input
                  type="number"
                  name="items[{{ $i }}][weight]"
                  class="form-control @error("items.$i.weight") is-invalid @enderror"
                  value="{{ $old['weight'] }}"
                  min="0" max="100"
                  required>
                @error("items.$i.weight")<div class="invalid-feedback">{{ $message }}</div>@enderror
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>

    <div class="mb-4">
      <button type="button" id="add-item" class="btn btn-sm btn-success">
        <i class="bi bi-plus-lg"></i> Add KPI Item
      </button>
      @error('items')<div class="text-danger mt-1">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary">Save Plan</button>
    <a href="{{ route('plans.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const table    = document.querySelector('#items-table tbody');
    const addBtn   = document.getElementById('add-item');
    let   rowIndex = table.rows.length;

    function addRow(metric = '', weight = '') {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>
          <input type="text"
                 name="items[${rowIndex}][metric]"
                 class="form-control"
                 value="${metric}"
                 required>
        </td>
        <td>
          <input type="number"
                 name="items[${rowIndex}][weight]"
                 class="form-control"
                 value="${weight}"
                 min="0" max="100"
                 required>
        </td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-danger btn-sm remove-row">&times;</button>
        </td>`;
      table.appendChild(tr);
      rowIndex++;
    }

    // always have at least one row
    if (rowIndex === 0) addRow();

    // add new row on click
    addBtn.addEventListener('click', () => addRow());

    // delegate removal
    table.addEventListener('click', e => {
      if (e.target.matches('.remove-row')) {
        e.target.closest('tr').remove();
      }
    });
  });
</script>
@endpush  {{-- this block will now be rendered by @stack('scripts') in your layout :contentReference[oaicite:2]{index=2}:contentReference[oaicite:3]{index=3} --}}
