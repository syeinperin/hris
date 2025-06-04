@extends('layouts.app')
@section('content')
<div class="container">
  <h2>All Evaluations Report</h2>
  <table class="table">
    <thead><tr>
      <th>Form</th><th>Employee</th><th>Evaluator</th><th>Date</th><th>Total Score</th>
    </tr></thead>
    <tbody>
      @foreach($evaluations as $ev)
      <tr>
        <td>{{ $ev->form->title }}</td>
        <td>{{ $ev->employee->user->name }}</td>
        <td>{{ $ev->evaluator->name }}</td>
        <td>{{ $ev->evaluated_on->format('Y-m-d') }}</td>
        <td>{{ $ev->total_score }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  {{ $evaluations->links() }}
</div>
@endsection
