@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Payroll Report</h2>

    <!-- Date Range Filter (optional) -->
    <form action="{{ route('payroll.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <label>Start Date</label>
            <input type="date" name="start_date" value="{{ $start_date }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label>End Date</label>
            <input type="date" name="end_date" value="{{ $end_date }}" class="form-control">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary mt-4">Generate Payroll</button>
        </div>
    </form>

    <table class="table">
    <thead>
        <tr>
            <th>Employee</th>
            <th>Designation</th>
            <th>Rate per Hour</th>
            <th>Total Hours</th>
            <th>Gross Pay</th>
            <th>Deduction</th>
            <th>Cash Advance</th>
            <th>Total Deduction</th>
            <th>Net Pay</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
            <tr>
                <td>{{ $employee->name }}</td>
                <td>{{ optional($employee->designation)->name ?? 'N/A' }}</td>
                <td>{{ number_format($employee->rate_per_hour, 2) }}</td>
                <td>{{ $employee->total_hours }}</td>
                <td>{{ number_format($employee->gross_pay, 2) }}</td>
                <td>{{ number_format($employee->deduction, 2) }}</td>
                <td>{{ number_format($employee->cash_advance, 2) }}</td>
                <td>{{ number_format($employee->total_deduction, 2) }}</td>
                <td>{{ number_format($employee->net_pay, 2) }}</td>
                <td>
                    <a href="{{ route('payroll.show', $employee->id) }}" class="btn btn-primary">View Payslip</a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
@endsection
