@extends('layouts.app')

@section('page_title','Performance Plans')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Performance Plans</h3>
    <a href="{{ route('plans.create') }}" class="btn btn-primary">+ New Plan</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th>
        <th>From</th>
        <th>Until</th>
        <th>Notes</th>
        <th style="width:120px">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($plans as $plan)
      <tr>
        <td>{{ $plan->name }}</td>
        <td>{{ optional($plan->effective_from)->format('Y-m-d') ?: '–' }}</td>
        <td>{{ optional($plan->effective_until)->format('Y-m-d') ?: '–' }}</td>
        <td>{{ \Illuminate\Support\Str::limit($plan->notes, 50, '…') }}</td>
        <td class="text-center">
          <a href="{{ route('plans.edit',$plan) }}" class="btn btn-sm btn-warning">Edit</a>
          <form action="{{ route('plans.destroy',$plan) }}"
                method="POST" class="d-inline"
                onsubmit="return confirm('Delete this plan?');">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="5" class="text-center text-muted py-3">No plans found.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  {{ $plans->links('pagination::bootstrap-5') }}
</div>
@endsection
