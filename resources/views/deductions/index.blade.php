@extends('layouts.app')

@section('page_title','Deduction Settings')

@section('content')
<div class="container">

  {{-- Title + “New” button --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Deduction Settings</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeductionModal">
      <i class="bi bi-plus-lg me-1"></i> New Deduction
    </button>
  </div>

  {{-- Success alert --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Search form --}}
  <form action="{{ route('deductions.index') }}" method="GET" class="mb-3">
    <div class="input-group">
      <input type="text"
             name="search"
             class="form-control"
             placeholder="Search description or employee…"
             value="{{ request('search') }}">
      <button class="btn btn-outline-secondary">
        <i class="bi bi-search"></i> Search
      </button>
    </div>
  </form>

  {{-- Deductions table --}}
  <div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th>Employee</th>
          <th>Description</th>
          <th>Amount</th>
          <th>From</th>
          <th>Until</th>
          <th>Notes</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($deductions as $d)
          <tr>
            <td>{{ $d->employee->name }}</td>
            <td>{{ $d->description }}</td>
            <td>₱{{ number_format($d->amount,2) }}</td>
            <td>{{ $d->effective_from->format('Y-m-d') }}</td>
            <td>{{ optional($d->effective_until)->format('Y-m-d') ?? '–' }}</td>
            <td>{{ $d->notes ?? '–' }}</td>
            <td class="text-center">
              <form action="{{ route('deductions.destroy', $d) }}"
                    method="POST" class="d-inline"
                    onsubmit="return confirm('Delete this deduction?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-danger">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              No deductions found.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-3">
    {{ $deductions->withQueryString()->links('pagination::bootstrap-5') }}
  </div>

  {{-- New Deduction Modal --}}
  <div class="modal fade" id="addDeductionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
      <div class="modal-content">
        <form action="{{ route('deductions.store') }}" method="POST">
          @csrf

          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-wallet-plus-fill me-1"></i> New Deduction
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
            {{-- Employee selector: All or one --}}
            <div class="mb-3 form-floating">
              <select name="employee_id"
                      id="employee_id"
                      class="form-select @error('employee_id') is-invalid @enderror"
                      required>
                <option value="all" {{ old('employee_id') == 'all' ? 'selected' : '' }}>
                  All Employees
                </option>
                @foreach($employees as $id => $name)
                  <option value="{{ $id }}"
                    {{ old('employee_id') == (string)$id ? 'selected' : '' }}>
                    {{ $name }}
                  </option>
                @endforeach
              </select>
              <label for="employee_id">Employee *</label>
              @error('employee_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Description --}}
            <div class="mb-3 form-floating">
              <input  type="text"
                      name="description"
                      id="description"
                      class="form-control @error('description') is-invalid @enderror"
                      placeholder="Description"
                      value="{{ old('description') }}"
                      required>
              <label for="description">Description *</label>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Amount --}}
            <div class="mb-3 form-floating">
              <input  type="number"
                      name="amount"
                      id="amount"
                      class="form-control @error('amount') is-invalid @enderror"
                      placeholder="Amount"
                      step="0.01"
                      value="{{ old('amount') }}"
                      required>
              <label for="amount">Amount *</label>
              @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Effective From --}}
            <div class="mb-3 form-floating">
              <input  type="date"
                      name="effective_from"
                      id="effective_from"
                      class="form-control @error('effective_from') is-invalid @enderror"
                      value="{{ old('effective_from', now()->toDateString()) }}"
                      required>
              <label for="effective_from">Effective From *</label>
              @error('effective_from')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Effective Until --}}
            <div class="mb-3 form-floating">
              <input  type="date"
                      name="effective_until"
                      id="effective_until"
                      class="form-control @error('effective_until') is-invalid @enderror"
                      value="{{ old('effective_until') }}">
              <label for="effective_until">Effective Until</label>
              @error('effective_until')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Notes --}}
            <div class="mb-3 form-floating">
              <textarea name="notes"
                        id="notes"
                        class="form-control @error('notes') is-invalid @enderror"
                        placeholder="Notes"
                        style="height:80px">{{ old('notes') }}</textarea>
              <label for="notes">Notes</label>
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div><!-- /.modal-body -->

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-circle-fill me-1"></i> Save
            </button>
            <button type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  {{-- /Modal --}}
</div>
@endsection
