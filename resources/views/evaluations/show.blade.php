@extends('layouts.app')

@section('page_title', 'Evaluate: ' . $employee->user->name)

@section('content')
<div class="container-fluid">
  <h1 class="h3 mb-4">Evaluate: {{ $employee->user->name }}</h1>

  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Please fix the following:</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('evaluations.store', [$form, $employee]) }}">
    @csrf

    <table class="table table-bordered align-middle">
      <thead class="table-light text-center">
        <tr>
          <th>No.</th>
          <th>Criteria</th>
          @foreach(\App\Models\PerformanceEvaluationDetail::ratingOptions() as $value => $label)
            <th title="{{ $label }}">{{ $label }}</th>
          @endforeach
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        @foreach($criteria as $i => $crit)
          <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $crit->text }}</td>

            @foreach(\App\Models\PerformanceEvaluationDetail::ratingOptions() as $value => $label)
              <td class="text-center">
                <input
                  type="radio"
                  name="ratings[{{ $crit->id }}]"
                  value="{{ $value }}"
                  {{ old("ratings.{$crit->id}") === $value ? 'checked' : '' }}
                  required
                >
              </td>
            @endforeach

            <td>
              <input
                type="text"
                name="remarks[{{ $crit->id }}]"
                class="form-control form-control-sm"
                value="{{ old("remarks.{$crit->id}") }}"
                placeholder="Optional"
              >
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="mb-4">
      <label for="comments" class="form-label">Overall Remarks</label>
      <textarea id="comments"
                name="comments"
                class="form-control"
                rows="3"
                placeholder="General comments">{{ old('comments') }}</textarea>
    </div>

    <button type="submit" class="btn btn-success">Submit Evaluation</button>
  </form>
</div>
@endsection
