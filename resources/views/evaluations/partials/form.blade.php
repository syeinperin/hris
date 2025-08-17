@php
  $empId   = old('employee_id', isset($evaluation)? $evaluation->employee_id : null);
  $empIds  = old('employee_ids', []);
  $start   = old('period_start', isset($evaluation)? $evaluation->period_start?->toDateString() : '');
  $end     = old('period_end',   isset($evaluation)? $evaluation->period_end?->toDateString()   : '');
  $remarks = old('remarks', isset($evaluation)? $evaluation->remarks : '');
@endphp

<div class="row g-3">
  @if(($mode ?? 'create') === 'create')
    <div class="col-md-6">
      <label class="form-label">Employees</label>
      <select name="employee_ids[]" id="employee_ids_create"
              class="form-select @error('employee_ids') is-invalid @enderror"
              multiple required size="8">
        @foreach($employees as $id => $name)
          <option value="{{ $id }}" {{ in_array((string)$id, array_map('strval', (array)$empIds)) ? 'selected' : '' }}>
            {{ $name }}
          </option>
        @endforeach
      </select>
      @error('employee_ids') <div class="invalid-feedback">{{ $message }}</div> @enderror
      <div class="small text-muted mt-1">
        Hold <strong>Ctrl</strong> (Win) / <strong>Cmd</strong> (Mac) to multi-select —
        <a href="#" id="selectAllEmployees">Select all</a> ·
        <a href="#" id="clearAllEmployees">Clear</a>
      </div>
    </div>
  @else
    <div class="col-md-6">
      <label class="form-label">Employee</label>
      <input value="{{ $evaluation->employee->name }}" class="form-control" disabled>
    </div>
  @endif

  <div class="col-md-3">
    <label class="form-label">Period Start</label>
    <input type="date" name="period_start" value="{{ $start }}"
           class="form-control @error('period_start') is-invalid @enderror" required>
    @error('period_start') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-3">
    <label class="form-label">Period End</label>
    <input type="date" name="period_end" value="{{ $end }}"
           class="form-control @error('period_end') is-invalid @enderror" required>
    @error('period_end') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-12">
    <div class="alert alert-info py-2">
      Rate each criterion from <strong>1</strong> (Unsatisfactory) to <strong>5</strong> (Excellent).
      Weighted total automatically computes to <strong>100%</strong>.
    </div>

    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:32%">Review Area</th>
            <th class="text-center" style="width:8%">Weight</th>
            <th class="text-center" style="width:15%">Score (1–5)</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $it)
            @php
              $cur  = old("scores.$it->id", isset($evaluation) ? optional($evaluation->scores->firstWhere('item_id',$it->id))->score : null);
              $note = old("notes.$it->id",  isset($evaluation) ? optional($evaluation->scores->firstWhere('item_id',$it->id))->notes : null);
            @endphp
            <tr>
              <td>
                <div class="fw-semibold">{{ $it->name }}</div>
                @if($it->description)
                  <div class="small text-muted">{{ $it->description }}</div>
                @endif
              </td>
              <td class="text-center fw-semibold">{{ $it->weight }}%</td>
              <td class="text-center">
                <select name="scores[{{ $it->id }}]" class="form-select form-select-sm w-auto mx-auto" required>
                  <option value="">-</option>
                  @for($i=1;$i<=5;$i++)
                    <option value="{{ $i }}" {{ (string)$cur===(string)$i?'selected':'' }}>{{ $i }}</option>
                  @endfor
                </select>
              </td>
              <td>
                <input name="notes[{{ $it->id }}]" value="{{ $note }}" class="form-control form-control-sm"
                       placeholder="Optional notes">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-12">
    <label class="form-label">Overall Remarks</label>
    <textarea name="remarks" rows="3" class="form-control">{{ $remarks }}</textarea>
  </div>
</div>
