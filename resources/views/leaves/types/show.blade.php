@extends('layouts.app')
@section('page_title','View Leave Type')

@section('content')
<div class="container">
  <h3>Leave Type Details</h3>
  <dl class="row">
    <dt class="col-sm-3">Name</dt>
    <dd class="col-sm-9">{{ $leaveType->name }}</dd>

    <dt class="col-sm-3">Default Days</dt>
    <dd class="col-sm-9">{{ $leaveType->default_days }}</dd>

    <dt class="col-sm-3">Description</dt>
    <dd class="col-sm-9">{{ $leaveType->description }}</dd>

    <dt class="col-sm-3">Active</dt>
    <dd class="col-sm-9">{{ $leaveType->is_active ? 'Yes' : 'No' }}</dd>
  </dl>
  <a href="{{ route('leave-types.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>
@endsection
