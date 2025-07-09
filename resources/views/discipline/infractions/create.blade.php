@extends('layouts.app')

@section('page_title','New Infraction Report')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">

      <div class="card shadow-sm">
        <div class="card-header bg-white">
          <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> New Infraction Report</h5>
        </div>
        <div class="card-body">
          <form method="POST" action="{{ route('discipline.infractions.store') }}">
            @csrf

            <div class="mb-3">
              <label class="form-label">Employee</label>
              <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                <option value="">— Select Employee —</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}" @selected(old('employee_id')==$e->id)>
                    {{ $e->user->name }}
                  </option>
                @endforeach
              </select>
              @error('employee_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Location</label>
              <input type="text"
                     name="location"
                     class="form-control @error('location') is-invalid @enderror"
                     value="{{ old('location') }}"
                     required>
              @error('location')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Date of Incident</label>
              <input type="date"
                     name="incident_date"
                     class="form-control @error('incident_date') is-invalid @enderror"
                     value="{{ old('incident_date') }}"
                     required>
              @error('incident_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Time of Incident</label>
              <input type="time"
                     name="incident_time"
                     class="form-control @error('incident_time') is-invalid @enderror"
                     value="{{ old('incident_time') }}">
              @error('incident_time')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description"
                        rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        required>{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-check mb-3">
              <input type="checkbox"
                     name="similar_before"
                     id="similar_before"
                     class="form-check-input"
                     @checked(old('similar_before'))>
              <label for="similar_before" class="form-check-label">
                Similar incident before?
              </label>
            </div>

            <div class="mb-3">
              <label class="form-label">Number of similar incidents</label>
              <input type="number"
                     name="similar_count"
                     class="form-control @error('similar_count') is-invalid @enderror"
                     value="{{ old('similar_count') }}">
              @error('similar_count')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-check mb-3">
              <input type="checkbox"
                     name="confidential"
                     id="confidential"
                     class="form-check-input"
                     @checked(old('confidential'))>
              <label for="confidential" class="form-check-label">
                Confidential?
              </label>
            </div>

            <div class="form-check mb-3">
              <input type="checkbox"
                     name="will_testify"
                     id="will_testify"
                     class="form-check-input"
                     @checked(old('will_testify'))>
              <label for="will_testify" class="form-check-label">
                Will testify?
              </label>
            </div>

            <div class="d-flex justify-content-between">
              <a href="{{ route('discipline.infractions.index') }}"
                 class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-success">
                Submit Report
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
