@extends('layouts.app')
@section('page_title','Payroll Calendar')

@section('content')
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
      Payroll Calendar » {{ \Carbon\Carbon::parse("$month-01")->format('F Y') }}
    </h3>
    <form method="GET" action="{{ route('payroll.calendar') }}" class="d-flex">
      <input
        type="text"
        name="search"
        class="form-control form-control-sm me-2"
        placeholder="Search code or name…"
        value="{{ $search }}"
      >
      <input
        type="month"
        name="month"
        class="form-control form-control-sm me-2"
        value="{{ $month }}"
      >
      <button class="btn btn-sm btn-outline-primary">Go</button>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered mb-1">
      <thead class="table-light">
        <tr>
          <th style="width:120px">Code</th>
          <th style="width:200px">Name</th>
          @for($d = $start->copy(); $d->lte($end); $d->addDay())
            <th class="text-center">{{ $d->day }}</th>
          @endfor
        </tr>
      </thead>
      <tbody>
        @foreach($employees as $emp)
          <tr>
            <td class="align-middle">{{ $emp->employee_code }}</td>
            <td class="align-middle">
              <a href="{{ route('employees.edit',$emp) }}">
                {{ $emp->last_name }}, {{ $emp->first_name }}
              </a>
            </td>

            @for($d = $start->copy(); $d->lte($end); $d->addDay())
              @php
                $day       = $d->toDateString();
                $dow       = $d->format('l');                         // e.g. "Sunday"
                $sched     = $emp->schedule;                          // eager-loaded
                $isRest    = $sched && $sched->rest_day === $dow;     // compare
                $rec       = $attendance
                              ->get($emp->id, collect())
                              ->get($day);
                $isHoliday = $holidays->has($day);
              @endphp

              @if($isHoliday)
                {{-- Official Holiday --}}
                <td class="p-0 bg-warning text-dark"
                    style="opacity:.5;width:32px;height:32px"
                    title="{{ $holidays[$day] }}">
                  &nbsp;
                </td>

              @elseif($isRest)
                {{-- Weekly Rest Day --}}
                <td class="p-0 bg-secondary text-white"
                    style="opacity:.5;width:32px;height:32px"
                    title="Rest Day">
                  &nbsp;
                </td>

              @else
                {{-- Attendance / Manual --}}
                <td class="p-0 position-relative cell"
                    data-emp="{{ $emp->id }}"
                    data-day="{{ $day }}"
                    style="cursor:pointer;width:32px;height:32px">
                  <div class="w-100 h-100 {{
                    $rec
                      ? ($rec->is_manual ? 'bg-primary' : 'bg-info')
                      : ''
                  }}"></div>
                </td>
              @endif
            @endfor

          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="d-flex justify-content-between mt-2">
    <div>
      Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }}
      of {{ $employees->total() }} employees
    </div>
    <div>{{ $employees->links() }}</div>
  </div>

  <div class="mt-4 d-flex flex-wrap align-items-center">
    <span class="badge bg-info me-2">Auto</span><small class="me-4">Real attendance</small>
    <span class="badge bg-primary me-2">Manual</span><small class="me-4">Manual override</small>
    <span class="badge bg-warning text-dark me-2">Holiday</span><small class="me-4">Official holiday</small>
    <span class="badge bg-secondary me-2">Rest Day</span><small>Weekend/rest</small>
  </div>
</div>
@endsection

@push('styles')
<style>
  .table td, .table th { padding: 0; font-size: .75rem; }
  .position-relative { position: relative; }
</style>
@endpush

@push('scripts')
<script>
  const token = document.head.querySelector('meta[name="csrf-token"]').content;
  document.querySelectorAll('td.cell').forEach(td => {
    td.addEventListener('click', async () => {
      const res = await fetch("{{ route('calendar.toggleManual') }}", {
        method: 'POST',
        headers: {
          'Content-Type':'application/json',
          'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({
          employee_id: td.dataset.emp,
          date:        td.dataset.day
        })
      });
      if (!res.ok) return alert('Toggle failed');
      const { manual } = await res.json();
      td.querySelector('div').className =
        'w-100 h-100 ' + (manual ? 'bg-primary' : 'bg-info');
    });
  });
</script>
@endpush
