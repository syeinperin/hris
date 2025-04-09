@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Payslip for {{ $employee->name }}</h2>
    <p><strong>Payroll Period:</strong> {{ $start_date }} to {{ $end_date }}</p>

    <table class="table table-bordered">
        <tr>
            <th>Designation</th>
            <td>{{ optional($employee->designation)->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Rate per Hour</th>
            <td>{{ number_format($employee->rate_per_hour, 2) }}</td>
        </tr>
        <tr>
            <th>Rate per Minute</th>
            <td>{{ number_format($employee->rate_per_minute, 2) }}</td>
        </tr>
        <tr>
            <th>Total Minutes Worked</th>
            <td>{{ number_format($employee->total_minutes, 2) }}</td>
        </tr>
        <tr>
            <th>Gross Pay</th>
            <td>{{ number_format($employee->gross_pay, 2) }}</td>
        </tr>
        <tr>
            <th>Deduction</th>
            <td>{{ number_format($employee->deduction, 2) }}</td>
        </tr>
        <tr>
            <th>Cash Advance</th>
            <td>{{ number_format($employee->cash_advance, 2) }}</td>
        </tr>
        <tr>
            <th>Total Deduction</th>
            <td>{{ number_format($employee->total_deduction, 2) }}</td>
        </tr>
        <tr>
            <th>Net Pay</th>
            <td>{{ number_format($employee->net_pay, 2) }}</td>
        </tr>
    </table>

    <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Back to Payroll Report</a>
</div>
@endsection
