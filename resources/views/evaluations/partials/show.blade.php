<div class="mb-2">
  <div class="row">
    <div class="col-md-6"><strong>Employee:</strong> {{ $evaluation->employee->name }}</div>
    <div class="col-md-3"><strong>Period:</strong> {{ $evaluation->period_start->toDateString() }} – {{ $evaluation->period_end->toDateString() }}</div>
    <div class="col-md-3"><strong>Overall:</strong> {{ number_format($evaluation->overall_score,2) }}%</div>
  </div>
  <div class="row">
    <div class="col-md-6"><strong>Evaluator:</strong> {{ $evaluation->evaluator->name ?? '—' }}</div>
    <div class="col-md-6"><strong>Status:</strong> {{ ucfirst($evaluation->status) }}</div>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm table-bordered align-middle">
    <thead class="table-light">
      <tr>
        <th>Review Area</th>
        <th class="text-center">Weight</th>
        <th class="text-center">Score (1–5)</th>
        <th class="text-center">Weighted Pts</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tbody>
      @foreach($evaluation->scores as $s)
        <tr>
          <td>{{ $s->item->name }}</td>
          <td class="text-center">{{ $s->weight_cache }}%</td>
          <td class="text-center">{{ $s->score }}</td>
          <td class="text-center">{{ number_format($s->weighted_score,3) }}</td>
          <td>{{ $s->notes }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

@if($evaluation->remarks)
  <div class="mt-2">
    <strong>Overall Remarks:</strong>
    <div class="border rounded p-2 bg-light">{{ $evaluation->remarks }}</div>
  </div>
@endif
