@extends('layouts.app')

@section('page_title','Disciplinary Actions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Disciplinary Actions</h4>
  <a href="{{ route('discipline.actions.create') }}" class="btn btn-primary">
    New Action
  </a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Infraction#</th>
      <th>Employee</th>
      <th>Date</th>
      <th>Type</th>
      <th class="text-end">Actions</th>
    </tr>
  </thead>
  <tbody>
    @forelse($actions as $a)
      <tr>
        <td>{{ $a->id }}</td>
        <td>#{{ $a->infraction->id }}</td>
        <td>{{ $a->infraction->employee->user->name }}</td>
        <td>{{ $a->action_date }}</td>
        <td>{{ $a->type->description }}</td>
        <td class="text-end">
          <a href="{{ route('discipline.actions.edit', $a) }}"
             class="btn btn-sm btn-outline-secondary">Edit</a>
          <form action="{{ route('discipline.actions.destroy', $a) }}"
                method="POST" class="d-inline"
                onsubmit="return confirm('Delete this action?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
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
@endsection
