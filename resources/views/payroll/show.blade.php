@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Payslip for {{ $employee->name }}</h2>
    <p><strong>Payroll Period:</strong> {{ $start_date }} to {{ $end_date }}</p>

    <table class="table table-bordered">
        <tr><th colspan="2">Employee Info</th></tr>
        <tr>
            <th>Designation</th>
            <td>{{ optional($employee->designation)->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Rate per Hour</th>
            <td>₱{{ number_format($employee->rate_per_hour, 2) }}</td>
        </tr>
        <tr>
            <th>Total Minutes Worked</th>
            <td>{{ number_format($employee->total_minutes, 2) }}</td>
        </tr>
        <tr>
            <th>Gross Pay</th>
            <td>₱{{ number_format($employee->gross_pay, 2) }}</td>
        </tr>

        <tr><th colspan="2">Government Deductions (Employee Share)</th></tr>
        <tr>
            <th>SSS</th>
            <td>₱{{ number_format($employee->sss, 2) }}</td>
        </tr>
        <tr>
            <th>PhilHealth</th>
            <td>₱{{ number_format($employee->philhealth, 2) }}</td>
        </tr>
        <tr>
            <th>Pag-IBIG</th>
            <td>₱{{ number_format($employee->pagibig, 2) }}</td>
        </tr>

        <tr><th colspan="2">Employer Contributions (Not Deducted)</th></tr>
        <tr>
            <th>SSS (Employer)</th>
            <td>₱{{ number_format($employee->sss_employer ?? 0, 2) }}</td>
        </tr>
        <tr>
            <th>PhilHealth (Employer)</th>
            <td>₱{{ number_format($employee->philhealth_employer ?? 0, 2) }}</td>
        </tr>
        <tr>
            <th>Pag-IBIG (Employer)</th>
            <td>₱{{ number_format($employee->pagibig_employer ?? 0, 2) }}</td>
        </tr>

        <tr>
            <th>Total Deduction</th>
            <td>₱{{ number_format($employee->total_deduction, 2) }}</td>
        </tr>
        <tr>
            <th>Cash Advance</th>
            <td>₱{{ number_format($employee->cash_advance, 2) }}</td>
        </tr>
        <tr>
            <th>Total Deduction (incl. Cash Advance)</th>
            <td>₱{{ number_format($employee->total_deduction + $employee->cash_advance, 2) }}</td>
        </tr>
        <tr>
            <th>Net Pay</th>
            <td><strong>₱{{ number_format($employee->net_pay, 2) }}</strong></td>
        </tr>
    </table>

    <a href="{{ route('payroll.index') }}" class="btn btn-secondary">Back to Payroll Report</a>
</div>
@endsection
