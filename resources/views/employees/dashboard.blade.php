{{-- resources/views/employees/dashboard.blade.php --}}
@extends('layouts.app')

@section('page_title','My Dashboard')

@section('content')
  <div class="container-fluid">
    <div class="row g-4">
      <x-dashboard-card 
        border="dark"
        icon="clock-fill text-dark"
        title="Hours Worked Today"
        :value="number_format($hoursWorked,2)"
      />

      <x-dashboard-card 
        border="dark"
        icon="person-dash text-dark"
        title="Absent Today?"
        :value="$absentToday ? 'Yes' : 'No'"
      />

      <x-dashboard-card 
        border="dark"
        icon="calendar3 text-dark"
        title="Pending Leave Requests"
        :value="$pendingLeaves"
        button-text="View Requests"
        :button-route="route('leaves.index')"
      />
    </div>

    @if($lastPunch)
      <div class="mt-5">
        <h5>
          Last Time-in: 
          {{ \Carbon\Carbon::parse($lastPunch->time_in)->format('g:i A, M j') }}
          @if($lastPunch->time_out)
            • Out: {{ \Carbon\Carbon::parse($lastPunch->time_out)->format('g:i A') }}
          @endif
        </h5>
      </div>
    @endif
  </div>
@endsection
