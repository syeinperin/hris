@extends('layouts.app')
@section('content')
<div class="container">
  <h2>My Evaluations</h2>
  <table class="table">
    <thead><tr><th>Form</th><th>Date</th><th>Total Score</th><th>Action</th></tr></thead>
    <tbody>
      @foreach($evaluations as $ev)
      <tr>
        <td>{{ $ev->form->title }}</td>
        <td>{{ $ev->evaluated_on->format('Y-m-d') }}</td>
        <td>{{ $ev->total_score }}</td>
        <td>
          <a href="{{ route('my.evaluations.show',$ev) }}" class="btn btn-sm btn-info">View</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  {{ $evaluations->links() }}
</div>
@endsection
