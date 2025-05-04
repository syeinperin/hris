<!DOCTYPE html>
<html>
<head>
    <title>Detailed Day-by-Day Payroll Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        h2, h3 {
            text-align: center;
        }
        p {
            margin: 5px 0 15px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 6px;
            text-align: left;
        }
        thead {
            background-color: #f5f5f5;
        }
        .daily-total-row {
            background: #fafafa;
        }
        hr {
            border: none;
            border-top: 1px solid #ccc;
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <h2>Detailed Day-by-Day Payroll Summary</h2>
    <p>Date Range: {{ $start_date }} to {{ $end_date }}</p>

    @foreach($employees as $emp)
        <h3>
            Employee: {{ $emp->name }}
            @if($emp->designation) ({{ $emp->designation->name }}) @endif
        </h3>
        
        @php
            // Group attendances by date
            $groupedByDay = $emp->attendances->groupBy(function($att) {
                return \Carbon\Carbon::parse($att->time_in)->format('Y-m-d');
            });

            $overallSeconds = 0;
            $overallPay     = 0;
            $ratePerHour    = $emp->designation->rate_per_hour ?? 0;
        @endphp

        @forelse($groupedByDay as $date => $records)
            @php
                $daySeconds = 0;
            @endphp

            <table>
                <thead>
                    <tr>
                        <th colspan="4">Date: {{ $date }}</th>
                    </tr>
                    <tr>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours Worked</th>
                        <th>Daily Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $attendance)
                        @if($attendance->time_in && $attendance->time_out)
                            @php
                                $timeIn    = \Carbon\Carbon::parse($attendance->time_in);
                                $timeOut   = \Carbon\Carbon::parse($attendance->time_out);

                                // handle overnight
                                if ($timeOut->lt($timeIn)) {
                                    $timeOut->addDay();
                                }

                                $diffSeconds    = $timeOut->diffInSeconds($timeIn);
                                $daySeconds    += $diffSeconds;
                                $durationHours  = round($diffSeconds / 3600, 2);

                                // PAY PER HOUR
                                $recordPay      = round($ratePerHour * $durationHours, 2);
                            @endphp
                            <tr>
                                <td>{{ $timeIn->format('H:i:s') }}</td>
                                <td>{{ $timeOut->format('H:i:s') }}</td>
                                <td>{{ $durationHours }} hrs</td>
                                <td>PHP {{ number_format($recordPay, 2) }}</td>
                            </tr>
                        @endif
                    @endforeach

                    @php
                        // daily totals
                        $overallSeconds += $daySeconds;
                        $dayHours       = round($daySeconds / 3600, 2);
                        $dayPay         = round($ratePerHour * $dayHours, 2);
                        $overallPay    += $dayPay;
                    @endphp
                    <tr class="daily-total-row">
                        <td colspan="2"><strong>Daily Total</strong></td>
                        <td><strong>{{ $dayHours }} hrs</strong></td>
                        <td><strong>PHP {{ number_format($dayPay, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        @empty
            <p>No attendance records for this employee within the date range.</p>
        @endforelse

        @php
            // grand totals for the period
            $totalHours = round($overallSeconds / 3600, 2);
            $grossPay   = round($ratePerHour * $totalHours, 2);

            // custom deductions (if any)
            $deduction      = 0;
            $cashAdvance    = 0;
            $totalDeduction = $deduction + $cashAdvance;
            $netPay         = round($grossPay - $totalDeduction, 2);
        @endphp

        <p><strong>Total Hours ({{ $emp->name }}):</strong> {{ $totalHours }} hrs</p>
        <p><strong>Gross Pay:</strong> PHP {{ number_format($grossPay, 2) }}</p>
        <p><strong>Total Deduction:</strong> PHP {{ number_format($totalDeduction, 2) }}</p>
        <p><strong>Net Pay:</strong> PHP {{ number_format($netPay, 2) }}</p>

        <hr>
    @endforeach
</body>
</html>
