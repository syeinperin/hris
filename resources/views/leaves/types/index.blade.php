@extends('layouts.app')
@section('page_title','Leave Types')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between mb-3">
    <h3>Leave Types</h3>
    <a href="{{ route('leave-types.create') }}" class="btn btn-primary">Add Leave Type</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Name</th>
        <th>Default Days</th>
        <th>Description</th>
        <th>Active</th>
        <th width="150">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($types as $type)
        <tr>
          <td>{{ $type->name }}</td>
          <td>{{ $type->default_days }}</td>
          <td>{{ $type->description }}</td>
          <td>{{ $type->is_active ? 'Yes' : 'No' }}</td>
          <td>
            <a href="{{ route('leave-types.show',$type) }}" class="btn btn-sm btn-info">View</a>
            <a href="{{ route('leave-types.edit',$type) }}" class="btn btn-sm btn-secondary">Edit</a>
            <form action="{{ route('leave-types.destroy',$type) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('Delete this type?');">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center">No leave types found.</td></tr>
      @endforelse
    </tbody>
  </table>

  {{ $types->links() }}
</div>
@endsection
