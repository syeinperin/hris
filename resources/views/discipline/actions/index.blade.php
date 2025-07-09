@extends('layouts.app')

@section('page_title','Disciplinary Actions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Disciplinary Actions</h4>
  <!-- trigger modal -->
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newActionModal">
    New Action
  </button>
</div>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Infraction#</th>
      <th>Employee</th>
      <th>Date</th>
      <th>Type</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @forelse($actions as $a)
      <tr>
        <td>{{ $a->id }}</td>
        <td>#{{ $a->infraction->id }}</td>
        <td>{{ $a->infraction->employee->user->name }}</td>
        <td>{{ $a->action_date->format('Y-m-d') }}</td>
        <td>{{ $a->type->description }}</td>
        <td>
          <a href="{{ route('discipline.actions.edit',$a) }}" class="btn btn-sm btn-outline-secondary">
            Edit
          </a>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="6" class="text-center text-muted">No actions yet.</td>
      </tr>
    @endforelse
  </tbody>
</table>

{{ $actions->links() }}


{{-- New Action Modal --}}
<div class="modal fade" id="newActionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('discipline.actions.store') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">New Disciplinary Action</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          {{-- Infraction dropdown --}}
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

          {{-- Action Type dropdown --}}
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

          {{-- Date --}}
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

          {{-- Checkboxes --}}
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="link_payroll" id="link_payroll"
              @checked(old('link_payroll'))>
            <label class="form-check-label" for="link_payroll">
              Link to Payroll
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="link_hiring" id="link_hiring"
              @checked(old('link_hiring'))>
            <label class="form-check-label" for="link_hiring">
              Link to Hiring Module
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="terminate_employee" id="terminate_employee"
              @checked(old('terminate_employee'))>
            <label class="form-check-label" for="terminate_employee">
              Terminate Employee
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary"
                  data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Action</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
