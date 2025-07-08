@extends('layouts.app')

@section('page_title','Dashboard')

@section('content')
<div class="container-fluid">
  <div class="row g-4">
    {{-- HR cards --}}
    @role('hr')
      <x-dashboard-card
        border="{{ $pendingUserCount>0?'info':'dark' }}"
        icon="check2-circle text-{{ $pendingUserCount>0?'info':'dark' }}"
        title="User Approvals"
        :value="$pendingUserCount"
        button-text="Review Users"
        :button-route="route('users.index')" />

      <x-dashboard-card
        border="dark"
        icon="person-dash text-dark"
        title="Today's Absentees"
        :value="$absentCount"
        button-text="View Absentees"
        :button-route="route('attendance.index')" />

      <x-dashboard-card
        border="{{ $endingCount>0?'warning':'dark' }}"
        icon="exclamation-triangle text-{{ $endingCount>0?'warning':'dark' }}"
        title="Contracts Ending"
        :value="$endingCount"
        button-text="View Endings"
        :button-route="route('employees.endings')" />

      <x-dashboard-card
        border="{{ $loanEndingCount>0?'danger':'dark' }}"
        icon="file-earmark-text text-{{ $loanEndingCount>0?'danger':'dark' }}"
        title="Loans Ending Soon"
        :value="$loanEndingCount"
        button-text="View Loans"
        :button-route="route('loans.index')" />
    @endrole

    {{-- Supervisor cards --}}
    @role('supervisor')
      <x-dashboard-card
        border="{{ $ongoing->isNotEmpty()?'primary':'dark' }}"
        icon="clock-history text-{{ $ongoing->isNotEmpty()?'primary':'dark' }}"
        title="Ongoing Evaluations"
        :value="$ongoing->count()"
        button-text="Go to Evaluations"
        :button-route="route('evaluations.index')" />

      <x-dashboard-card
        border="{{ $pendingLeaveCount>0?'warning':'dark' }}"
        icon="calendar-exclamation text-{{ $pendingLeaveCount>0?'warning':'dark' }}"
        title="Pending Leave Req’s"
        :value="$pendingLeaveCount"
        button-text="Review Leaves"
        :button-route="route('leaves.index')" />
    @endrole
  </div>

  {{-- Announcements & Reminders --}}
  <div class="row mt-4 gx-4">
    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Latest Announcements</div>
        <ul class="list-group list-group-flush">
          @forelse($announcements as $a)
            <li class="list-group-item">
              <strong>{{ $a->title }}</strong><br>
              <small class="text-muted">{{ $a->created_at->format('M d, Y') }}</small>
            </li>
          @empty
            <li class="list-group-item text-center text-muted">
              No announcements yet.
            </li>
          @endforelse
        </ul>
        @can('create', \App\Models\Announcement::class)
          <div class="card-footer text-end">
            <a href="{{ route('announcements.create') }}"
               class="btn btn-sm btn-primary">New Announcement</a>
          </div>
        @endcan
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Reminders</div>
        <div class="card-body">
          @if($birthdays->isEmpty() && $anniversaries->isEmpty())
            <p class="text-muted mb-0">No upcoming birthdays or anniversaries.</p>
          @else
            @if($birthdays->isNotEmpty())
              <p class="mb-2"><strong>
                {{ $birthdays->count() }} birthday{{ $birthdays->count()>1?'s':'' }} coming up:
              </strong></p>
              <ul class="mb-4">
                @foreach($birthdays as $b)
                  <li>{{ $b->name }} — {{ $b->dob->format('M d') }}</li>
                @endforeach
              </ul>
            @endif

            @if($anniversaries->isNotEmpty())
              <p class="mb-2"><strong>
                {{ $anniversaries->count() }} anniversar{{ $anniversaries->count()>1?'ies':'y' }} coming up:
              </strong></p>
              <ul class="mb-0">
                @foreach($anniversaries as $a)
                  <li>
                    {{ $a->name }} — {{ $a->employment_start_date->format('M d') }}
                    ({{ $a->service_years }} {{ Str::plural('year',$a->service_years) }})
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
@endsection
