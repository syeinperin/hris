@extends('layouts.app')

@section('page_title', 'My Dashboard')

@section('content')
<div class="container-fluid">

  {{-- STATS CARDS --}}
  <div class="row g-4">
    <x-dashboard-card
      border="dark"
      icon="clock-fill text-dark"
      title="Hours Worked Today"
      :value="number_format($hoursWorked, 2)"
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

  {{-- LAST PUNCH --}}
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

  {{-- LEAVE SUMMARY --}}
  <div class="mt-5">
    <h3 class="mb-3">Leave Summary for {{ $year }}</h3>

    @if($allocations->isEmpty())
      <p class="text-muted">No leave allocations found for {{ $year }}.</p>
    @else
      @php
        $fmt = fn($n) => rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
      @endphp

      <div class="table-responsive bg-white shadow rounded">
        <table class="table mb-0">
          <thead class="table-light">
            <tr>
              <th>Leave Type</th>
              <th class="text-center">Entitled (days)</th>
              <th class="text-center">Taken (days)</th>
              <th class="text-center">Balance (days)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($allocations as $alloc)
              <tr>
                <td>{{ $alloc->leaveType->name }}</td>
                <td class="text-center">{{ $fmt($alloc->entitled_days) }}</td>
                <td class="text-center">{{ $fmt($alloc->taken_days) }}</td>
                <td class="text-center {{ $alloc->balance_days < 0 ? 'text-danger' : '' }}">
                  {{ $fmt($alloc->balance_days) }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

</div>

{{-- Include viewer in case you add announcement links here later --}}
@include('components.announcement-viewer')
@endsection
