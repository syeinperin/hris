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
