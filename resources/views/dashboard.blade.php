{{-- File: resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('page_title','Dashboard')

@section('content')
<div class="container-fluid">

  {{-- Top summary cards --}}
  <div class="row g-4">
    @role('hr')
      {{-- User Approvals --}}
      <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center">
            <i class="bi bi-check2-circle fs-2 text-{{ $pendingUserCount > 0 ? 'info' : 'secondary' }} me-3"></i>
            <div>
              <h5 class="mb-0">{{ $pendingUserCount }}</h5>
              <small class="text-muted">User Approvals</small><br>
              <a href="{{ route('approvals.index') }}"
                 class="btn btn-sm btn-{{ $pendingUserCount > 0 ? 'info' : 'secondary' }} mt-2">
                Review Approvals
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- Today's Absentees --}}
      <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center">
            <i class="bi bi-person-dash fs-2 text-{{ $absentCount > 0 ? 'warning' : 'secondary' }} me-3"></i>
            <div>
              <h5 class="mb-0">{{ $absentCount }}</h5>
              <small class="text-muted">Today's Absentees</small><br>
              <a href="{{ route('attendance.index') }}"
                 class="btn btn-sm btn-{{ $absentCount > 0 ? 'warning' : 'secondary' }} mt-2">
                View Absentees
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- Contracts Ending --}}
      <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center">
            <i class="bi bi-exclamation-triangle fs-2 text-{{ $endingCount > 0 ? 'warning' : 'secondary' }} me-3"></i>
            <div>
              <h5 class="mb-0">{{ $endingCount }}</h5>
              <small class="text-muted">Contracts Ending</small><br>
              <a href="{{ route('employees.endings') }}"
                 class="btn btn-sm btn-{{ $endingCount > 0 ? 'warning' : 'secondary' }} mt-2">
                View Endings
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- Loans Ending Soon --}}
      <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center">
            <i class="bi bi-file-earmark-text fs-2 text-{{ $loanEndingCount > 0 ? 'danger' : 'secondary' }} me-3"></i>
            <div>
              <h5 class="mb-0">{{ $loanEndingCount }}</h5>
              <small class="text-muted">Loans Ending Soon</small><br>
              <a href="{{ route('loans.index') }}"
                 class="btn btn-sm btn-{{ $loanEndingCount > 0 ? 'danger' : 'secondary' }} mt-2">
                View Loans
              </a>
            </div>
          </div>
        </div>
      </div>
    @endrole

    @role('supervisor')
      {{-- Ongoing Evaluations --}}
      <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center">
            <i class="bi bi-clock-history fs-2 text-{{ $ongoing->isNotEmpty() ? 'primary' : 'secondary' }} me-3"></i>
            <div>
              <h5 class="mb-0">{{ $ongoing->count() }}</h5>
              <small class="text-muted">Ongoing Evaluations</small><br>
              <a href="{{ route('evaluations.index') }}"
                 class="btn btn-sm btn-{{ $ongoing->isNotEmpty() ? 'primary' : 'secondary' }} mt-2">
                Go to Evaluations
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- Pending Leave Requests --}}
      <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-body d-flex align-items-center">
            <i class="bi bi-calendar-exclamation fs-2 text-{{ $pendingLeaveCount > 0 ? 'warning' : 'secondary' }} me-3"></i>
            <div>
              <h5 class="mb-0">{{ $pendingLeaveCount }}</h5>
              <small class="text-muted">Pending Leave Req’s</small><br>
              <a href="{{ route('leaves.index') }}"
                 class="btn btn-sm btn-{{ $pendingLeaveCount > 0 ? 'warning' : 'secondary' }} mt-2">
                Review Leaves
              </a>
            </div>
          </div>
        </div>
      </div>
    @endrole
  </div>

  {{-- === Mini Analytics on Dashboard === --}}
  <div class="card shadow-sm border-0 mt-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0 fw-bold">Analytics</h5>
        <a href="{{ route('reports.analytics') }}" class="btn btn-sm btn-outline-primary">
          Open full Analytics
        </a>
      </div>

      {{-- KPI tiles --}}
      <div class="mini-grid mb-3">
        <div class="card border-0 bg-light p-3">
          <div class="kpi">
            <div class="num" id="kpiHeadcount">--</div>
            <div><div>Headcount</div><small class="text-muted">current</small></div>
          </div>
        </div>
        <div class="card border-0 bg-light p-3">
          <div class="kpi">
            <div class="num" id="kpiAbs">--%</div>
            <div><div>Absenteeism</div><small class="text-muted">latest month</small></div>
          </div>
        </div>
        <div class="card border-0 bg-light p-3">
          <div class="kpi">
            <div class="num" id="kpiTTH">--</div>
            <div><div>Time-to-hire</div><small class="text-muted">avg days</small></div>
          </div>
        </div>
        <div class="card border-0 bg-light p-3">
          <div class="kpi">
            <div class="num" id="kpiOT">--</div>
            <div><div>OT Cost</div><small class="text-muted">latest month</small></div>
          </div>
        </div>
      </div>

      {{-- Tiny charts --}}
      <div class="charts-grid">
        <div class="card border-0 p-3">
          <div class="fw-semibold mb-2">Headcount trend</div>
          <canvas id="miniHeadcount" class="mini-chart"></canvas>
        </div>
        <div class="card border-0 p-3">
          <div class="fw-semibold mb-2">Turnover (separations)</div>
          <canvas id="miniTurnover" class="mini-chart"></canvas>
        </div>
      </div>
    </div>
  </div>

  {{-- Announcements & Reminders --}}
  <div class="row mt-4 gx-4">
    {{-- Latest Announcements --}}
    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
          Latest Announcements
        </div>
        <ul class="list-group list-group-flush">
          @forelse($announcements as $a)
            <li class="list-group-item">
              <a href="{{ route('announcements.show', $a) }}" data-view-announcement class="text-decoration-none">
                <strong>{{ $a->title }}</strong>
              </a><br>
              <small class="text-muted">{{ ($a->published_at ?? $a->created_at)->format('M d, Y') }}</small>
            </li>
          @empty
            <li class="list-group-item text-center text-muted">
              No announcements yet.
            </li>
          @endforelse
        </ul>
        @can('create', \App\Models\Announcement::class)
          <div class="card-footer bg-white text-end">
            <a href="{{ route('announcements.create') }}" class="btn btn-sm btn-primary">
              New Announcement
            </a>
          </div>
        @endcan
      </div>
    </div>

    {{-- Reminders --}}
    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
          Reminders
        </div>
        <div class="card-body">
          @if($birthdays->isEmpty() && $anniversaries->isEmpty())
            <p class="text-muted mb-0">No upcoming birthdays or anniversaries.</p>
          @else
            @if($birthdays->isNotEmpty())
              <p class="mb-2">
                <strong>
                  {{ $birthdays->count() }} birthday{{ $birthdays->count() > 1 ? 's' : '' }} coming up:
                </strong>
              </p>
              <ul class="mb-4">
                @foreach($birthdays as $b)
                  <li>{{ $b->name }} — {{ $b->dob->format('M d') }}</li>
                @endforeach
              </ul>
            @endif

            @if($anniversaries->isNotEmpty())
              <p class="mb-2">
                <strong>
                  {{ $anniversaries->count() }} anniversar{{ $anniversaries->count() > 1 ? 'ies' : 'y' }} coming up:
                </strong>
              </p>
              <ul class="mb-0">
                @foreach($anniversaries as $a)
                  <li>
                    {{ $a->name }} — {{ $a->employment_start_date->format('M d') }}
                    ({{ $a->service_years }} {{ Str::plural('year', $a->service_years) }})
                  </li>
                @endforeach
              </ul>
            @endif
          @endif
        </div>
      </div>
    </div>
  </div>

</div>

{{-- Reuse the same viewer modal --}}
@include('components.announcement-viewer')
@endsection

@push('styles')
<style>
  .mini-grid{display:grid;gap:14px;grid-template-columns:repeat(4,minmax(160px,1fr))}
  .charts-grid{display:grid;gap:14px;grid-template-columns:repeat(2,minmax(260px,1fr))}
  .kpi{display:flex;align-items:center;gap:.8rem}
  .kpi .num{font-weight:800;font-size:1.6rem;line-height:1}
  .mini-chart{height:220px}
  @media (max-width:992px){
    .mini-grid{grid-template-columns:repeat(2,1fr)}
    .charts-grid{grid-template-columns:1fr}
  }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(async () => {
  try {
    const res = await fetch('{{ route('dashboard.analytics.json') }}', {
      headers:{'X-Requested-With':'XMLHttpRequest'}
    });
    const { cards, series } = await res.json();

    // KPIs
    document.getElementById('kpiHeadcount').textContent =
      (cards.headcount_now ?? 0).toLocaleString();
    document.getElementById('kpiAbs').textContent =
      ((cards.absenteeism_pct ?? 0).toFixed(2)) + '%';
    document.getElementById('kpiTTH').textContent =
      (cards.avg_time_to_hire ?? 0).toFixed(1);
    document.getElementById('kpiOT').textContent =
      (cards.ot_cost ?? 0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});

    // Headcount trend
    new Chart(document.getElementById('miniHeadcount'), {
      type: 'line',
      data: {
        labels: (series.headcount || []).map(x => x.month),
        datasets: [{
          label:'Headcount',
          data:(series.headcount || []).map(x => x.total),
          borderWidth:2, tension:.3, fill:false
        }]
      },
      options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
    });

    // Turnover bar
    new Chart(document.getElementById('miniTurnover'), {
      type: 'bar',
      data: {
        labels: (series.turnover || []).map(x => x.month),
        datasets: [{
          label:'Separations',
          data:(series.turnover || []).map(x => x.separations),
          borderWidth:1
        }]
      },
      options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}} }
    });
  } catch (e) {
    console.error('Mini analytics failed:', e);
  }
})();
</script>
@endpush
