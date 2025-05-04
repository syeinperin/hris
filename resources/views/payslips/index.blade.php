@extends('layouts.app')

@section('page_title','My Payslips')

@section('content')
<div class="container-fluid">
  <h1 class="h3 mb-4">My Payslips</h1>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Generate form --}}
  <form action="{{ route('payslips.store') }}" method="POST" class="row g-3 mb-4">
    @csrf
    <div class="col-auto">
      <label class="form-label">Start</label>
      <input type="date" name="period_start" class="form-control" required
             value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}">
    </div>
    <div class="col-auto">
      <label class="form-label">End</label>
      <input type="date" name="period_end" class="form-control" required
             value="{{ old('period_end', now()->endOfMonth()->toDateString()) }}">
    </div>
    <div class="col-auto align-self-end">
      <button class="btn btn-primary">Generate</button>
    </div>
  </form>

  {{-- List --}}
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Period</th>
        <th>Hours</th>
        <th>OT Pay</th>
        <th>Deductions</th>
        <th>Gross</th>
        <th>Net</th>
        <th>PDF</th>
      </tr>
    </thead>
    <tbody>
      @forelse($payslips as $slip)
        <tr>
          <td>
            {{ $slip->period_start->format('M j, Y') }}
            – {{ $slip->period_end->format('M j, Y') }}
          </td>
          <td>{{ number_format($slip->worked_hours,2) }}</td>
          <td>{{ number_format($slip->ot_pay,2) }}</td>
          <td>{{ number_format($slip->deductions,2) }}</td>
          <td>{{ number_format($slip->gross_amount,2) }}</td>
          <td>{{ number_format($slip->net_amount,2) }}</td>
          <td>
            <a href="{{ route('payslips.download',$slip) }}"
               class="btn btn-sm btn-outline-primary">
              PDF
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-muted">
            No payslips found.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{ $payslips->links() }}
</div>
@endsection
