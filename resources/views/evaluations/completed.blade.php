@extends('layouts.app')

@section('page_title', 'Completed Evaluations')

@section('content')
<div class="container-fluid">
  <h1 class="h3 mb-4">Completed Evaluations</h1>

  @if($completed->isEmpty())
    <div class="alert alert-info">You haven’t submitted any evaluations yet.</div>
  @else
    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>Form</th>
          <th>Employee</th>
          <th>Date</th>
          <th>Total Score</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($completed as $ev)
          <tr>
            <td>{{ $ev->form->title }}</td>
            <td>{{ $ev->employee->user->name }}</td>
            <td>{{ $ev->evaluated_on->format('Y-m-d') }}</td>
            <td>{{ $ev->total_score }}</td>
            <td>
              <a href="{{ route('my.evaluations.show', $ev->id) }}"
                 class="btn btn-info btn-sm">View</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection
