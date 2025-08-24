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
  {{-- NEW: Discipline summary chips --}}
  @isset($disciplineSummary)
    <div class="mt-2">
      @php $ds = $disciplineSummary; @endphp
      <span class="badge {{ ($ds['violations'] ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }} me-1">
        {{ $ds['violations'] ?? 0 }} Violation{{ ($ds['violations']??0) > 1 ? 's' : '' }}
      </span>
      <span class="badge {{ ($ds['suspension_days'] ?? 0) > 0 ? 'bg-warning text-dark' : 'bg-secondary' }}">
        {{ $ds['suspension_days'] ?? 0 }} Suspension day{{ ($ds['suspension_days']??0) > 1 ? 's' : '' }}
      </span>
    </div>
  @endisset
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

{{-- NEW: Violations & Suspensions in this period --}}
<div class="mt-4">
  <h6 class="mb-2">
    <i class="bi bi-exclamation-triangle me-1"></i>
    Violations & Suspensions (within period)
  </h6>

  @if(($periodActions ?? collect())->isEmpty())
    <div class="text-muted">No disciplinary records for this period.</div>
  @else
    <div class="table-responsive">
      <table class="table table-sm table-striped table-bordered">
        <thead class="table-light">
          <tr>
            <th style="white-space:nowrap;">Type</th>
            <th>Category</th>
            <th>Severity</th>
            <th>Points</th>
            <th>Status</th>
            <th style="white-space:nowrap;">Date / Range</th>
            <th>Days</th>
            <th>Reason / Notes</th>
          </tr>
        </thead>
        <tbody>
          @foreach($periodActions as $a)
            @php
              $isSusp = $a->action_type === 'suspension';
              $days   = $isSusp ? $a->suspensionDaysInRange($evaluation->period_start, $evaluation->period_end) : 0;
            @endphp
            <tr>
              <td>
                <span class="badge {{ $isSusp ? 'bg-warning text-dark' : 'bg-danger' }}">
                  {{ ucfirst($a->action_type) }}
                </span>
              </td>
              <td>{{ $a->category ?? '—' }}</td>
              <td>{{ ucfirst($a->severity) }}</td>
              <td>{{ $a->points ?? '—' }}</td>
              <td>{{ ucfirst($a->status) }}</td>
              <td>
                @if($isSusp)
                  {{ optional($a->start_date)->toDateString() }} – {{ optional($a->end_date)->toDateString() }}
                @else
                  {{ optional($a->created_at)->toDateString() }}
                @endif
              </td>
              <td class="text-center">{{ $days ?: '—' }}</td>
              <td>
                <div><strong>Reason:</strong> {{ $a->reason }}</div>
                @if($a->notes)
                  <div class="text-muted small">{{ $a->notes }}</div>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
