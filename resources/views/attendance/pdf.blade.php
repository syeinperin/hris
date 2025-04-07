<!DOCTYPE html>
<html>
<head>
    <title>Attendance PDF</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #eee;
        }
    </style>
</head>
<body>
    <h2>Attendance Records</h2>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Scheduled Time In</th>
                <th>Scheduled Time Out</th>
                <th>Actual Time In</th>
                <th>Actual Time Out</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->employee_id }}</td>
                <td>{{ $attendance->employee->name ?? 'N/A' }}</td>
                <td>
                    @if($attendance->schedule)
                        {{ \Carbon\Carbon::parse($attendance->schedule->time_in)->format('h:i A') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($attendance->schedule)
                        {{ \Carbon\Carbon::parse($attendance->schedule->time_out)->format('h:i A') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($attendance->time_in)
                        {{ \Carbon\Carbon::parse($attendance->time_in)->format('h:i:s A') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($attendance->time_out)
                        {{ \Carbon\Carbon::parse($attendance->time_out)->format('h:i:s A') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $attendance->created_at->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
