{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('page_title','Dashboard')

@section('content')
  <div class="container-fluid">
    <div class="row g-4">
      <x-dashboard-card 
        border="dark"                       {{-- sidebar color --}}
        icon="people-fill text-dark"        {{-- icon also gets that color --}}
        title="Total Employees"
        :value="$employeeCount"
        button-text="View Details"
        :button-route="route('employees.index')"
      />

      <x-dashboard-card 
        border="dark"
        icon="hourglass-split text-dark"
        title="Approvals"
        :value="$pendingApprovalsCount"
        button-text="View Pending"
        :button-route="route('approvals.index')"
      />

      <x-dashboard-card 
        border="dark"
        icon="calendar-check text-dark"
        title="Leave Requests"
        :value="$pendingLeaveRequestsCount"
        button-text="View Leaves"
        :button-route="route('approvals.index','t=leave')"
      />

      <x-dashboard-card 
        border="dark"
        icon="clock-history text-dark"
        title="Today's Absentees"
        :value="$absentCount"
        button-text="View Absentees"
        :button-route="route('attendance.index')"
      />
    </div>
  </div>
@endsection
