@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Edit Form: {{ $form->title }}</h2>

  <form method="POST" action="{{ route('performance_forms.update', $form) }}">
    @csrf @method('PUT')

    {{-- Title --}}
    <div class="mb-3">
      <label class="form-label">Title</label>
      <input type="text" name="title"
             class="form-control @error('title') is-invalid @enderror"
             value="{{ old('title',$form->title) }}" required>
      @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Description --}}
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea name="description"
                class="form-control @error('description') is-invalid @enderror"
                rows="3">{{ old('description',$form->description) }}</textarea>
      @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Evaluator --}}
    <div class="mb-3">
      <label class="form-label">Evaluator</label>
      <select name="evaluator_id"
              class="form-select @error('evaluator_id') is-invalid @enderror"
              required>
        <option value="" disabled>— Select Supervisor —</option>
        @foreach(\App\Models\User::all()->filter->hasRole('supervisor') as $sup)
          <option value="{{ $sup->id }}"
            {{ old('evaluator_id',$form->evaluator_id) == $sup->id ? 'selected' : '' }}>
            {{ $sup->name }}
          </option>
        @endforeach
      </select>
      @error('evaluator_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Employees --}}
    <div class="mb-3">
      <label class="form-label">Assign to Employees</label>
      <select name="employee_ids[]"
              class="form-select @error('employee_ids') is-invalid @enderror"
              multiple required>
        @php
          $assigned = $form->assignments->pluck('employee_id')->toArray();
        @endphp
        @foreach(\App\Models\Employee::with('user')->get() as $emp)
          <option value="{{ $emp->id }}"
            {{ in_array($emp->id, old('employee_ids',$assigned)) ? 'selected' : '' }}>
            {{ $emp->user->name }}
          </option>
        @endforeach
      </select>
      <div class="form-text">Hold Ctrl/Cmd to select multiple</div>
      @error('employee_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Criteria --}}
    <h5>Criteria</h5>
    <table class="table" id="criteria-table">
      <thead>
        <tr><th>Text</th><th>Initial Score</th><th></th></tr>
      </thead>
      <tbody>
        @foreach($form->criteria as $i => $c)
        <tr>
          <td>
            <input type="hidden" name="criteria[{{ $i }}][id]" value="{{ $c->id }}">
            <input type="text" name="criteria[{{ $i }}][text]"
                   class="form-control" value="{{ $c->text }}" required>
          </td>
          <td>
            <input type="number" name="criteria[{{ $i }}][default_score]"
                   class="form-control" value="{{ $c->default_score }}" required>
          </td>
          <td><button type="button" class="btn btn-sm btn-danger remove-row">✕</button></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <button type="button" id="add-criteria" class="btn btn-sm btn-secondary mb-3">
      + Add Criterion
    </button>

    <button type="submit" class="btn btn-primary">Update Form</button>
  </form>
</div>

@push('scripts')
<script>
  // same JS as create.blade…
  document.getElementById('add-criteria').addEventListener('click', () => {
    const tbody = document.querySelector('#criteria-table tbody');
    const idx   = tbody.children.length;
    const row   = document.createElement('tr');
    row.innerHTML = `
      <td><input type="text" name="criteria[${idx}][text]"
                 class="form-control" required></td>
      <td><input type="number" name="criteria[${idx}][default_score]"
                 class="form-control" required></td>
      <td><button type="button" class="btn btn-sm btn-danger remove-row">✕</button></td>
    `;
    tbody.appendChild(row);
  });
  document.querySelector('#criteria-table').addEventListener('click', e => {
    if (e.target.classList.contains('remove-row')) {
      e.target.closest('tr').remove();
    }
  });
</script>
@endpush
@endsection
