<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Probation Complete</title>
    <style>
      body { font-family: Arial, sans-serif; line-height: 1.6; }
      .container { padding: 20px; }
      h2 { color: #2c3e50; }
      p { margin-bottom: 1em; }
    </style>
</head>
<body>
<div class="container">
    <h2>Hello {{ $name }},</h2>

    <p>Congratulations! As of {{ $date }}, your probationary period (Employee Code: {{ $code }}) has ended, and you are now officially a <strong>Regular</strong> employee in our HR database. Thank you for all your hard work during probation!</p>

    <p>Wishing you continued success in your role.</p>

    <p>Best regards,<br>Human Resources Team</p>
</div>
</body>
</html>
