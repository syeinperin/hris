@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-4">Edit Deduction</h2>

  <form action="{{ route('deductions.update', $deduction) }}" method="POST">
    @csrf
    @method('PUT')

    {{-- Employee --}}
    <div class="mb-3">
      <label class="form-label">Employee</label>
      <select name="employee_id" class="form-control" required>
        @foreach($employees as $id => $name)
          <option value="{{ $id }}"
            {{ $deduction->employee_id == $id ? 'selected' : '' }}>
            {{ $name }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Description --}}
    <div class="mb-3">
      <label class="form-label">Description</label>
      <input name="description"
             type="text"
             class="form-control"
             value="{{ $deduction->description }}"
             required>
    </div>

    {{-- Amount --}}
    <div class="mb-3">
      <label class="form-label">Amount</label>
      <input name="amount"
             type="number"
             step="0.01"
             class="form-control"
             value="{{ $deduction->amount }}"
             required>
    </div>

        {{-- Effective From --}}
    <div class="col-md-6 mb-3">
    <label class="form-label">Effective From</label>
    <input
        name="effective_from"
        type="date"
        class="form-control"
        value="{{ old(
        'effective_from',
        optional($deduction->date)->format('Y-m-d')
        ) }}"
        required>
    </div>

    {{-- Effective Until --}}
    <div class="col-md-6 mb-3">
    <label class="form-label">Effective Until</label>
    <input
        name="effective_until"
        type="date"
        class="form-control"
        value="{{ old(
        'effective_until',
        optional($deduction->date)->format('Y-m-d')
        ) }}"
        required>
    </div>

    {{-- Notes --}}
    <div class="mb-3">
      <label class="form-label">Notes</label>
      <textarea name="notes"
                rows="3"
                class="form-control">{{ $deduction->notes }}</textarea>
    </div>

    <button class="btn btn-success">Update</button>
    <a href="{{ route('deductions.index') }}" class="btn btn-secondary">Cancel</a>
  </form>
</div>
@endsection
