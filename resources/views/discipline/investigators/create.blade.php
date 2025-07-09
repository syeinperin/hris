@extends('layouts.app')

@section('page_title','Infraction Investigators')

@section('content')
  <div class="d-flex justify-content-between mb-3">
    <h4>Investigators</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newInvestigatorModal">
      New Investigator
    </button>
  </div>

  <table class="table table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Infraction#</th>
        <th>Employee</th>
        <th>Investigator</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse($investigators as $inv)
        <tr>
          <td>{{ $inv->id }}</td>
          <td>#{{ $inv->infraction->id }}</td>
          <td>{{ $inv->infraction->employee->user->name }}</td>
          <td>{{ $inv->investigator->name }}</td>
          <td>
            <a href="{{ route('discipline.investigators.edit',$inv) }}"
               class="btn btn-sm btn-outline-secondary">Edit</a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="text-center text-muted">No investigators assigned yet.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{ $investigators->links() }}

  {{-- New Investigator Modal --}}
  <div class="modal fade" id="newInvestigatorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" action="{{ route('discipline.investigators.store') }}">
        @csrf
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Assign New Investigator</h5>
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

            {{-- Investigator (user) dropdown --}}
            <div class="mb-3">
              <label class="form-label">Investigator (User)</label>
              <select name="user_id"
                      class="form-select @error('user_id') is-invalid @enderror"
                      required>
                <option value="">— Select —</option>
                @foreach($users as $u)
                  <option value="{{ $u->id }}"
                    @selected(old('user_id')==$u->id)>
                    {{ $u->name }} ({{ $u->email }})
                  </option>
                @endforeach
              </select>
              @error('user_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="modal-footer">
            <button type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Assign</button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endsection
