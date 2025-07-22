@extends('layouts.app')

@section('page_title','New Disciplinary Action')

@section('content')
<div class="container py-4">
  <div class="card">
    <div class="card-header">New Disciplinary Action</div>
    <div class="card-body">
      <form method="POST" action="{{ route('discipline.actions.store') }}">
        @csrf

        {{-- Infraction Report --}}
        <div class="mb-3">
          <label class="form-label">Infraction Report</label>
          <select name="infraction_report_id"
                  class="form-select @error('infraction_report_id') is-invalid @enderror"
                  required>
            <option value="">— Select —</option>
            @foreach($infractions as $inf)
              <option value="{{ $inf->id }}"
                @selected(old('infraction_report_id')==$inf->id)>
                #{{ $inf->id }} — {{ $inf->employee->user->name }}
              </option>
            @endforeach
          </select>
          @error('infraction_report_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Action Type --}}
        <div class="mb-3">
          <label class="form-label">Action Type</label>
          <select name="action_type_id"
                  class="form-select @error('action_type_id') is-invalid @enderror"
                  required>
            <option value="">— Select —</option>
            @foreach($types as $type)
              <option value="{{ $type->id }}"
                @selected(old('action_type_id')==$type->id)>
                {{ $type->code }} — {{ $type->description }}
              </option>
            @endforeach
          </select>
          @error('action_type_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Action Date --}}
        <div class="mb-3">
          <label class="form-label">Action Date</label>
          <input type="date"
                 name="action_date"
                 class="form-control @error('action_date') is-invalid @enderror"
                 value="{{ old('action_date', now()->toDateString()) }}"
                 required>
          @error('action_date')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>

        {{-- Flags --}}
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox"
                 name="link_payroll" id="link_payroll"
                 {{ old('link_payroll') ? 'checked' : '' }}>
          <label class="form-check-label" for="link_payroll">
            Link to Payroll
          </label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox"
                 name="link_hiring" id="link_hiring"
                 {{ old('link_hiring') ? 'checked' : '' }}>
          <label class="form-check-label" for="link_hiring">
            Link to Hiring Module
          </label>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox"
                 name="terminate_employee" id="terminate_employee"
                 {{ old('terminate_employee') ? 'checked' : '' }}>
          <label class="form-check-label" for="terminate_employee">
            Terminate Employee
          </label>
        </div>

        <button type="submit" class="btn btn-success">Create Action</button>
        <a href="{{ route('discipline.actions.index') }}"
           class="btn btn-secondary ms-2">Cancel</a>
      </form>
    </div>
  </div>
</div>
@endsection
