{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('page_title','Dashboard')

@section('content')
<div class="container-fluid">
  
  {{-- ── CARDS GRID ───────────────────────────────────────────── --}}
  <div class="row g-4">
    @php $role = auth()->user()->role->name ?? ''; @endphp

    {{-- ========== ADMIN ============ --}}
    @if(auth()->user()->hasRole('admin'))
      <x-dashboard-card 
        border="dark" icon="hourglass-split text-dark"
        title="Approvals" :value="$pendingApprovalsCount"
        button-text="View Pending" :button-route="route('approvals.index')"
      />

      <x-dashboard-card 
        border="dark" icon="file-text text-danger"
        title="Audit Logs" :value="$logsCount"
        button-text="Review Logs" :button-route="route('audit-logs.index')"
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
    @endif

    {{-- ======== SUPERVISOR ======== --}}
    @if(auth()->user()->hasRole('supervisor'))
      <x-dashboard-card 
        border="dark" icon="calendar-check text-dark"
        title="Leave Requests" :value="$pendingLeaveRequestsCount"
        button-text="View Leaves" :button-route="route('leaves.index')"
      />
      <x-dashboard-card 
        border="dark" icon="hourglass-split text-dark"
        title="Approvals" :value="$pendingApprovalsCount"
        button-text="View Pending" :button-route="route('approvals.index')"
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
    @endif

    {{-- ========== HR ============ --}}
    @if(auth()->user()->hasRole('hr'))
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
    @endif

    {{-- ======== TIMEKEEPER ======== --}}
    @if(auth()->user()->hasRole('timekeeper'))
      <x-dashboard-card 
        border="dark" icon="person-dash text-dark"
        title="Today's Absentees" :value="$absentCount"
        button-text="View Absentees" :button-route="route('attendance.index')"
      />
      <x-dashboard-card 
        border="dark" icon="calendar3 text-dark"
        title="Schedules" :value="$scheduleCount"
        button-text="View Schedules" :button-route="route('schedule.index')"
      />
    @endif

  </div>
  {{-- /CARDS GRID ─────────────────────────────────────────────── --}}
  

  {{-- ── LATEST ANNOUNCEMENTS ──────────────────────────────────── --}}
  <div class="card mt-4">
    <div class="card-header">Latest Announcements</div>
    <ul class="list-group list-group-flush">
      @php
        $announcements = \App\Models\Announcement::latest()->take(5)->get();
      @endphp

      @forelse($announcements as $announcement)
        <li class="list-group-item">
          <a href="{{ route('announcements.show', $announcement) }}">
            <strong>{{ $announcement->title }}</strong>
          </a>
          <br>
          <small class="text-muted">
            {{ $announcement->created_at->format('M d, Y') }}
          </small>
        </li>
      @empty
        <li class="list-group-item text-center">No announcements yet.</li>
      @endforelse
    </ul>
    @can('create', \App\Models\Announcement::class)
      <div class="card-footer text-end">
        <a href="{{ route('announcements.create') }}" class="btn btn-sm btn-primary">
          New Announcement
        </a>
      </div>
    @endcan
  </div>
  {{-- /LATEST ANNOUNCEMENTS ──────────────────────────────────── --}}

</div>
@endsection
