<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contract Ending Notification</title>
    <style>
      body { font-family: Arial, sans-serif; line-height: 1.6; }
      .container { padding: 20px; }
      h2 { color: #2c3e50; }
      ul { list-style: disc; margin-left: 20px; }
      .section { margin-bottom: 20px; }
      .footer { margin-top: 40px; font-size: 0.9em; color: #555; }
    </style>
</head>
<body>
<div class="container">
    <h2>Contract Ending Notification</h2>
    <p><strong>Date:</strong> {{ $today }}</p>

    <div class="section">
      <p><strong>Employees with contracts ending within {{ $days }} days:</strong></p>
      @if($upcoming->count())
        <ul>
          @foreach($upcoming as $emp)
            <li>
              {{ $emp->name }} ({{ $emp->employee_code }}) — 
              Ends on {{ $emp->employment_end_date->format('M d, Y') }} 
              ({{ ucfirst($emp->employment_type) }})
            </li>
          @endforeach
        </ul>
      @else
        <p><em>None</em></p>
      @endif
    </div>

    <div class="section">
      <p><strong>Employees whose contracts have already expired:</strong></p>
      @if($expired->count())
        <ul>
          @foreach($expired as $emp)
            <li>
              {{ $emp->name }} ({{ $emp->employee_code }}) — 
              Expired on {{ $emp->employment_end_date->format('M d, Y') }} 
              ({{ ucfirst($emp->employment_type) }})
            </li>
          @endforeach
        </ul>
      @else
        <p><em>None</em></p>
      @endif
    </div>

    <div class="footer">
      <p>--<br>
      Regards,<br>
      Your HRIS System</p>
    </div>
</div>
</body>
</html>
