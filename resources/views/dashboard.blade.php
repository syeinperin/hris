@extends('layouts.app')

@section('page_title', 'Dashboard')

@section('content')
<div class="container-fluid">

  {{-- ── CARDS GRID ─────────────────────────────────────────────────── --}}
  <div class="row g-4">
    @role('hr')
      <x-dashboard-card
        border="dark" icon="person-dash text-dark"
        title="Today's Absentees" :value="$absentCount"
        button-text="View Absentees" :button-route="route('attendance.index')"
      />
      <x-dashboard-card
        border="dark" icon="people-fill text-dark"
        title="Total Employees" :value="$employeeCount"
        button-text="View Details" :button-route="route('employees.index')"
      />
      <x-dashboard-card
        border="dark" icon="building text-dark"
        title="Departments" :value="$departmentCount"
        button-text="Manage Departments" :button-route="route('departments.index')"
      />
      <x-dashboard-card
        border="dark" icon="award text-dark"
        title="Designations" :value="$designationCount"
        button-text="Manage Designations" :button-route="route('designations.index')"
      />
      @if($endingCount > 0)
        <x-dashboard-card
          border="warning" icon="exclamation-triangle text-warning"
          title="Endings Soon" :value="$endingCount"
          button-text="View Endings" :button-route="route('employees.endings')"
        />
      @endif
    @endrole
    {{-- …other roles… --}}
  </div>
  {{-- ── /CARDS GRID ────────────────────────────────────────────────── --}}

  {{-- ── ANNOUNCEMENTS & REMINDERS ROW ─────────────────────────────── --}}
  <div class="row mt-4 gx-4">

    {{-- ANNOUNCEMENTS --}}
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

    {{-- REMINDERS --}}
    <div class="col-md-6">
      <div class="card shadow-sm mb-4">
        <div class="card-header">Reminders</div>
        <div class="card-body">
          @if($birthdays->isEmpty() && $anniversaries->isEmpty())
            <p class="text-muted mb-0">No upcoming birthdays or anniversaries.</p>
          @else

            {{-- Birthdays --}}
            @if($birthdays->isNotEmpty())
              <p class="mb-2">
                <strong>
                  {{ $birthdays->count() }}
                  birthday{{ $birthdays->count() > 1 ? 's' : '' }} coming up:
                </strong>
              </p>
              <ul class="mb-4">
                @foreach($birthdays as $emp)
                  <li>{{ $emp->name }} — {{ $emp->dob->format('M d') }}</li>
                @endforeach
              </ul>
            @endif

            {{-- Anniversaries --}}
            @if($anniversaries->isNotEmpty())
              <p class="mb-2">
                <strong>
                  {{ $anniversaries->count() }}
                  anniversar{{ $anniversaries->count() > 1 ? 'ies' : 'y' }} coming up:
                </strong>
              </p>
              <ul class="mb-0">
                @foreach($anniversaries as $emp)
                  <li>
                    {{ $emp->name }}
                    — {{ $emp->employment_start_date->format('M d') }}
                    ({{ $emp->service_years }}
                     {{ \Illuminate\Support\Str::plural('year', $emp->service_years) }})
                  </li>
                @endforeach
              </ul>
            @endif

          @endif
        </div>
      </div>
    </div>

  </div>
  {{-- ── /ANNOUNCEMENTS & REMINDERS ROW ────────────────────────────── --}}

</div>
@endsection
