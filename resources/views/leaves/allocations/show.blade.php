@extends('layouts.app')
@section('page_title','View Leave Allocation')

@section('content')
<div class="container">
  <h3>Allocation Details</h3>
  <dl class="row">
    <dt class="col-sm-3">Employee</dt>
    <dd class="col-sm-9">{{ $leaveAllocation->employee->name }}</dd>

    <dt class="col-sm-3">Leave Type</dt>
    <dd class="col-sm-9">{{ $leaveAllocation->leaveType->name }}</dd>

    <dt class="col-sm-3">Year</dt>
    <dd class="col-sm-9">{{ $leaveAllocation->year }}</dd>

    <dt class="col-sm-3">Days Allocated</dt>
    <dd class="col-sm-9">{{ $leaveAllocation->days_allocated }}</dd>

    <dt class="col-sm-3">Days Used</dt>
    <dd class="col-sm-9">{{ $leaveAllocation->days_used }}</dd>
  </dl>
  <a href="{{ route('leave-allocations.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>
@endsection
