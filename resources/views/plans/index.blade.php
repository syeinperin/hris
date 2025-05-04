@extends('layouts.app')

@section('content')
<div class="container">
  <h3>Performance Plans</h3>
  <a href="{{ route('plans.create') }}" class="btn btn-primary mb-3">+ New Plan</a>
  <table class="table table-bordered">
    <thead>
      <tr><th>Name</th><th>Start</th><th>End</th></tr>
    </thead>
    <tbody>
      @forelse($plans as $p)
        <tr>
          <td>{{ $p->name }}</td>
          <td>{{ optional($p->start_date)->format('Y-m-d') ?: '—' }}</td>
          <td>{{ optional($p->end_date)->format('Y-m-d')   ?: '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="3">No plans found.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
