@extends('layouts.app')

@section('page_title', 'Request Leave')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Leave Request</h1>
    <a href="{{ route('leaves.index') }}" class="btn btn-secondary">
      ← Back to My Requests
    </a>
  </div>

  <form action="{{ route('leaves.store') }}" method="POST" class="row g-3">
    @csrf

    <div class="col-md-4">
      <label for="start_date" class="form-label">From</label>
      <input
        type="date"
        id="start_date"
        name="start_date"
        class="form-control @error('start_date') is-invalid @enderror"
        value="{{ old('start_date') }}"
        required
      >
      @error('start_date')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-4">
      <label for="end_date" class="form-label">To</label>
      <input
        type="date"
        id="end_date"
        name="end_date"
        class="form-control @error('end_date') is-invalid @enderror"
        value="{{ old('end_date') }}"
        required
      >
      @error('end_date')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-12">
      <label for="reason" class="form-label">Reason</label>
      <textarea
        id="reason"
        name="reason"
        rows="4"
        class="form-control @error('reason') is-invalid @enderror"
        required
      >{{ old('reason') }}</textarea>
      @error('reason')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-12">
      <button type="submit" class="btn btn-primary">Submit Request</button>
    </div>
  </form>
</div>
@endsection
