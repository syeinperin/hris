@extends('layouts.app')
@section('page_title','Edit Leave Allocation')

@section('content')
<div class="container">
  <h3>Edit Leave Allocation</h3>
  <form action="{{ route('leave-allocations.update',$leaveAllocation) }}" method="POST">
    @csrf @method('PUT')

    <div class="mb-3">
      <label>Employee</label>
      <select name="employee_id"
              class="form-select @error('employee_id') is-invalid @enderror"
              required>
        @foreach($employees as $id => $name)
          <option value="{{ $id }}"
            {{ old('employee_id',$leaveAllocation->employee_id)==$id?'selected':'' }}>
            {{ $name }}
          </option>
        @endforeach
      </select>
      @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label>Leave Type</label>
      <select name="leave_type_id"
              class="form-select @error('leave_type_id') is-invalid @enderror"
              required>
        @foreach($types as $id => $name)
          <option value="{{ $id }}"
            {{ old('leave_type_id',$leaveAllocation->leave_type_id)==$id?'selected':'' }}>
            {{ $name }}
          </option>
        @endforeach
      </select>
      @error('leave_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label>Year</label>
      <input type="number"
             name="year"
             class="form-control @error('year') is-invalid @enderror"
             value="{{ old('year',$leaveAllocation->year) }}"
             required>
      @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label>Days Allocated</label>
      <input type="number"
             name="days_allocated"
             min="0"
             class="form-control @error('days_allocated') is-invalid @enderror"
             value="{{ old('days_allocated',$leaveAllocation->days_allocated) }}"
             required>
      @error('days_allocated')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label>Days Used</label>
      <input type="number"
             name="days_used"
             min="0"
             max="{{ old('days_allocated',$leaveAllocation->days_allocated) }}"
             class="form-control @error('days_used') is-invalid @enderror"
             value="{{ old('days_used',$leaveAllocation->days_used) }}"
             required>
      @error('days_used')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button class="btn btn-success">Update</button>
    <a href="{{ route('leave-allocations.index') }}" class="btn btn-outline-secondary">Cancel</a>
  </form>
</div>
@endsection
