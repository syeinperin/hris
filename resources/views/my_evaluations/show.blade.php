@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Result: {{ $evaluation->form->title }}</h2>
  <p>Evaluated on: {{ $evaluation->evaluated_on->format('Y-m-d') }}</p>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>No.</th>
        <th>Criteria</th>
        <th>Initial</th>
        <th>Score</th>
      </tr>
    </thead>
    <tbody>
      @foreach($details as $d)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $d->criterion->text }}</td>
          <td>{{ $d->criterion->default_score }}</td>
          <td>{{ $d->weighted_score }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="2"><strong>Total</strong></td>
        <td colspan="2"><strong>{{ $evaluation->total_score }}</strong></td>
      </tr>
    </tbody>
  </table>

  <a href="{{ route('my.evaluations.index') }}" class="btn btn-secondary">
    ← Back to My Evaluations
  </a>
</div>
@endsection
