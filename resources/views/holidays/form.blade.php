@extends('layouts.app')
@section('page_title', $holiday->exists ? 'Edit Holiday' : 'Add Holiday')

@section('content')
<div class="container">
  <h3>{{ $holiday->exists ? 'Edit Holiday' : 'Add Holiday' }}</h3>

  <form method="POST"
        action="{{ $holiday->exists
                    ? route('holidays.update',$holiday)
                    : route('holidays.store') }}">
    @csrf
    @if($holiday->exists)
      @method('PUT')
    @endif

    {{-- Name --}}
    <div class="mb-3">
      <label for="name" class="form-label">Holiday Name</label>
      <input
        type="text"
        id="name"
        name="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name',$holiday->name) }}"
        required
      >
      @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Date --}}
    <div class="mb-3">
      <label for="date" class="form-label">Date</label>
      <input
        type="date"
        id="date"
        name="date"
        class="form-control @error('date') is-invalid @enderror"
        value="{{ old('date',$holiday->date?->format('Y-m-d')) }}"
        required
      >
      @error('date')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Type --}}
    <div class="mb-3">
      <label for="type" class="form-label">Type</label>
      <select
        id="type"
        name="type"
        class="form-select @error('type') is-invalid @enderror"
        required
      >
        <option value="">-- select --</option>
        <option value="regular" {{ old('type',$holiday->type)=='regular' ? 'selected':'' }}>
          Regular
        </option>
        <option value="special" {{ old('type',$holiday->type)=='special' ? 'selected':'' }}>
          Special
        </option>
      </select>
      @error('type')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    {{-- Recurring --}}
    <div class="form-check mb-3">
      {{-- always send a 0 if unchecked --}}
      <input type="hidden" name="is_recurring" value="0">
      <input
        class="form-check-input"
        type="checkbox"
        id="is_recurring"
        name="is_recurring"
        value="1"
        {{ old('is_recurring',$holiday->is_recurring) ? 'checked':'' }}
      >
      <label class="form-check-label" for="is_recurring">
        Recurring annually
      </label>
    </div>

    <button type="submit" class="btn btn-primary">
      {{ $holiday->exists ? 'Update' : 'Create' }}
    </button>
    <a href="{{ route('holidays.index') }}" class="btn btn-outline-secondary">
      Cancel
    </a>
  </form>
</div>
@endsection
