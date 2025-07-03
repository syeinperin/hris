@extends('layouts.app')

@section('page_title','My Dashboard')

@section('content')
  <div class="container-fluid">
    {{-- ── STATS CARDS ─────────────────────────────────────────── --}}
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
    {{-- ── /STATS CARDS ────────────────────────────────────────── --}}

    {{-- ── LAST PUNCH ─────────────────────────────────────────── --}}
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
    {{-- ── /LAST PUNCH ────────────────────────────────────────── --}}

    {{-- ── LEAVE SUMMARY ───────────────────────────────────────── --}}
    <div class="mt-5">
      <h3 class="mb-3">Leave Summary for {{ $year }}</h3>

      @if($allocations->isEmpty())
        <p class="text-gray-600">No leave allocations found for {{ $year }}.</p>
      @else
        {{-- helper to trim trailing “.00” --}}
        @php
          $fmt = fn($v) => rtrim(rtrim(number_format($v,2, '.', ''), '0'), '.');
        @endphp

        <div class="overflow-x-auto bg-white shadow rounded">
          <table class="min-w-full">
            <thead class="bg-gray-100 text-left">
              <tr>
                <th class="px-4 py-2">Leave Type</th>
                <th class="px-4 py-2 text-center">Entitled (days)</th>
                <th class="px-4 py-2 text-center">Taken (days)</th>
                <th class="px-4 py-2 text-center">Balance (days)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($allocations as $alloc)
                <tr class="border-t">
                  <td class="px-4 py-2">{{ $alloc->leaveType->name }}</td>
                  <td class="px-4 py-2 text-center">{{ $fmt($alloc->entitled_days) }}</td>
                  <td class="px-4 py-2 text-center">{{ $fmt($alloc->taken_days) }}</td>
                  <td class="px-4 py-2 text-center {{ $alloc->balance_days < 0 ? 'text-red-600':'' }}">
                    {{ $fmt($alloc->balance_days) }}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
    {{-- ── /LEAVE SUMMARY ─────────────────────────────────────── --}}
  </div>
@endsection
