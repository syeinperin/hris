@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Assigned Evaluations</h2>
  <table class="table">
    <thead>
      <tr><th>Form</th><th>Employee</th><th>Action</th></tr>
    </thead>
    <tbody>
      @foreach($assignments as $a)
      <tr>
        <td>{{ $a->form->title }}</td>
        <td>{{ $a->employee->user->name }}</td>
        <td>
          <a href="{{ route('evaluations.show', [$a->form->id, $a->employee->id]) }}"
             class="btn btn-primary btn-sm">Evaluate</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection
