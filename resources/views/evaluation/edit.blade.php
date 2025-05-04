@extends('layouts.app')

@section('page_title', isset($plan) ? 'Edit Plan' : 'New Plan')

@section('content')
<div class="container">
  <h3>{{ isset($plan) ? 'Edit' : 'New' }} Performance Plan</h3>

  <form method="POST"
        action="{{ isset($plan) ? route('plans.update',$plan) : route('plans.store') }}">
    @csrf
    @if(isset($plan))
      @method('PUT')
    @endif

    <div class="mb-3 form-floating">
      <input  type="text"
              name="name"
              id="name"
              class="form-control @error('name') is-invalid @enderror"
              placeholder="Plan Name"
              value="{{ old('name',$plan->name ?? '') }}"
              required>
      <label for="name">Plan Name *</label>
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3 form-floating">
      <input  type="date"
              name="effective_from"
              id="effective_from"
              class="form-control @error('effective_from') is-invalid @enderror"
              value="{{ old('effective_from', optional($plan->effective_from)->toDateString()) }}">
      <label for="effective_from">Effective From</label>
      @error('effective_from')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3 form-floating">
      <input  type="date"
              name="effective_until"
              id="effective_until"
              class="form-control @error('effective_until') is-invalid @enderror"
              value="{{ old('effective_until', optional($plan->effective_until)->toDateString()) }}">
      <label for="effective_until">Effective Until</label>
      @error('effective_until')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="mb-3 form-floating">
      <textarea name="notes"
                id="notes"
                class="form-control @error('notes') is-invalid @enderror"
                placeholder="Notes"
                style="height:100px">{{ old('notes',$plan->notes ?? '') }}</textarea>
      <label for="notes">Notes</label>
      @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <button type="submit" class="btn btn-success">
      {{ isset($plan)? 'Update' : 'Save' }}
    </button>
    <a href="{{ route('plans.index') }}" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
@endsection
