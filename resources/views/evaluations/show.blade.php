{{-- resources/views/evaluations/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <h2>Evaluate: {{ $employee->user->name }}</h2>

  <form method="POST" action="{{ route('evaluations.store', [$form, $employee]) }}">
    @csrf

    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th style="width:2rem">No.</th>
          <th>Criteria</th>
          {{-- Loop headers from the ratingOptions() map --}}
          @foreach(\App\Models\PerformanceEvaluationDetail::ratingOptions() as $letter => $label)
            <th style="width:3rem" title="{{ $label }}">{{ $letter }}</th>
          @endforeach
          <th>Remarks</th>
        </tr>
      </thead>
      <tbody>
        @foreach($criteria as $i => $criterion)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $criterion->text }}</td>

          {{-- Radio buttons for each rating option --}}
          @foreach(\App\Models\PerformanceEvaluationDetail::ratingOptions() as $letter => $label)
            <td class="text-center">
              <input
                type="radio"
                name="ratings[{{ $criterion->id }}]"
                value="{{ $letter }}"
                {{ old("ratings.{$criterion->id}") === $letter ? 'checked' : '' }}
                required
              >
            </td>
          @endforeach

          {{-- Per‐criterion remarks --}}
          <td>
            <input
              type="text"
              name="remarks[{{ $criterion->id }}]"
              class="form-control form-control-sm @error("remarks.{$criterion->id}") is-invalid @enderror"
              value="{{ old("remarks.{$criterion->id}") }}"
              placeholder="Optional remarks"
            >
            @error("remarks.{$criterion->id}")
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Overall remarks --}}
    <div class="mb-4">
      <label for="comments" class="form-label">Overall Remarks</label>
      <textarea
        name="comments"
        id="comments"
        class="form-control @error('comments') is-invalid @enderror"
        rows="3"
        placeholder="General comments about this evaluation"
      >{{ old('comments') }}</textarea>
      @error('comments')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>

    <button type="submit" class="btn btn-success">
      Submit Evaluation
    </button>
  </form>
</div>
@endsection
