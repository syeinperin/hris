@extends('layouts.app')

@section('page_title','Infraction Reports')

@section('content')
<div class="container-fluid">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Infraction Reports</h3>
    <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#newInfractionModal">
      New Report
    </button>
  </div>

  {{-- Table of existing --}}
  <div class="card mb-4">
    <div class="card-body p-0">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Reported By</th>
            <th>Date</th>
            <th>Location</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reports as $rep)
            <tr>
              <td>{{ $rep->id }}</td>
              <td>{{ $rep->employee->user->name }}</td>
              <td>{{ $rep->reporter->name }}</td>
              <td>{{ $rep->incident_date }}</td>
              <td>{{ $rep->location }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center">No records</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="d-flex justify-content-center">
    {{ $reports->links() }}
  </div>
</div>

{{-- New Report Modal --}}
<div class="modal fade" id="newInfractionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST"
            action="{{ route('discipline.infractions.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">New Infraction Report</h5>
          <button type="button" class="btn-close"
                  data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          {{-- Employee --}}
          <div class="mb-3">
            <label class="form-label">Employee</label>
            <select name="employee_id"
                    class="form-select @error('employee_id') is-invalid @enderror"
                    required>
              <option value="">— Select —</option>
              @foreach($employees as $e)
                <option value="{{ $e->id }}"
                  @selected(old('employee_id')==$e->id)>
                  {{ $e->user->name }}
                </option>
              @endforeach
            </select>
            @error('employee_id')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Reported By --}}
          <div class="mb-3">
            <label class="form-label">Reported By</label>
            <select name="reported_by"
                    class="form-select @error('reported_by') is-invalid @enderror"
                    required>
              <option value="">— Select —</option>
              @foreach($users as $u)
                <option value="{{ $u->id }}"
                  @selected(old('reported_by')==$u->id)>
                  {{ $u->name }}
                </option>
              @endforeach
            </select>
            @error('reported_by')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Date / Time --}}
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Incident Date</label>
              <input type="date"
                     name="incident_date"
                     class="form-control @error('incident_date') is-invalid @enderror"
                     value="{{ old('incident_date') }}"
                     required>
              @error('incident_date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Incident Time</label>
              <input type="time"
                     name="incident_time"
                     class="form-control @error('incident_time') is-invalid @enderror"
                     value="{{ old('incident_time') }}">
              @error('incident_time')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- Location --}}
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

          {{-- Description --}}
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description"
                      rows="4"
                      class="form-control @error('description') is-invalid @enderror"
                      required
            >{{ old('description') }}</textarea>
            @error('description')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Flags --}}
          <div class="form-check mb-2">
            <input type="checkbox"
                   name="similar_before"
                   id="similar_before"
                   class="form-check-input"
                   @checked(old('similar_before'))>
            <label class="form-check-label" for="similar_before">
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
          <div class="form-check mb-2">
            <input type="checkbox"
                   name="confidential"
                   id="confidential"
                   class="form-check-input"
                   @checked(old('confidential'))>
            <label class="form-check-label" for="confidential">
              Confidential?
            </label>
          </div>
          <div class="form-check mb-0">
            <input type="checkbox"
                   name="will_testify"
                   id="will_testify"
                   class="form-check-input"
                   @checked(old('will_testify'))>
            <label class="form-check-label" for="will_testify">
              Will testify?
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            Submit Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
