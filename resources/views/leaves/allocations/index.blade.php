@extends('layouts.app')
@section('page_title','Leave Allocations')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between mb-3">
    <h3>Leave Allocations</h3>
    <a href="{{ route('leave-allocations.create') }}" class="btn btn-primary">Add Allocation</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Employee</th>
        <th>Leave Type</th>
        <th>Year</th>
        <th>Allocated</th>
        <th>Used</th>
        <th width="150">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($allocations as $alloc)
        <tr>
          <td>{{ $alloc->employee->name }}</td>
          <td>{{ $alloc->leaveType->name }}</td>
          <td>{{ $alloc->year }}</td>
          <td>{{ $alloc->days_allocated }}</td>
          <td>{{ $alloc->days_used }}</td>
          <td>
            <a href="{{ route('leave-allocations.show',$alloc) }}" class="btn btn-sm btn-info">View</a>
            <a href="{{ route('leave-allocations.edit',$alloc) }}" class="btn btn-sm btn-secondary">Edit</a>
            <form action="{{ route('leave-allocations.destroy',$alloc) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('Delete this allocation?');">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center">No allocations found.</td></tr>
      @endforelse
    </tbody>
  </table>

  {{ $allocations->links() }}
</div>
@endsection
