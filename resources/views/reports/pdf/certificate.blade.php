<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Certificate of Employment – {{ $employee->employee_code }}</title>
  <style>
    @page { margin: 50px; }

    body {
      font-family: "Times New Roman", serif;
      color: #333;
      line-height: 1.6;
    }

    .container { text-align: center; margin-top: 0; }
    .logo { max-height: 80px; margin-bottom: 10px; }

    .company-info {
      font-size: 14px; font-weight: bold; margin-bottom: 30px; line-height: 1.2;
    }

    h1 { font-size: 28px; margin-bottom: 0; font-weight: bold; }
    .subtitle { font-size: 14px; margin-top: 4px; margin-bottom: 30px; color: #555; }
    .highlight-name { font-size: 20px; font-weight: bold; margin: 10px 0 30px; }

    .content { font-size: 14px; width: 80%; margin: 0 auto 40px; text-align: justify; }
    .content p { margin-bottom: 18px; }

    .footer { width: 80%; margin: 40px auto 0; text-align: left; font-size: 14px; }
    .signature { margin-top: 60px; display: inline-block; text-align: center; }
    .signature-line { border-top: 1px solid #000; width: 200px; margin-bottom: 6px; }
  </style>
</head>
<body>
  <div class="container">
    @if(file_exists(public_path('images/asiatex-logo.png')))
      <img src="{{ public_path('images/asiatex-logo.png') }}" class="logo" alt="Asiatex Logo">
    @endif

    <div class="company-info">
      Asia Textile Mills, Inc.<br>
      Old National Highway, Bgy San Cristobal,<br>
      Calamba, Laguna, Philippines<br>
      (049) 531 7239 | asiatex84@gmail.com
    </div>

    <h1>Certificate of Employment</h1>
    <p class="subtitle">This is to certify that</p>
    <p class="highlight-name">{{ strtoupper($employee->name) }}</p>
  </div>

  <div class="content">
    <p>
      has been employed by <strong>Asia Textile Mills, Inc.</strong> as
      <strong>{{ optional($employee->designation)->name }}</strong>
      from
      <strong>{{ optional($employee->employment_start_date)->format('F Y') }}</strong>
      up to the present. He/She has consistently demonstrated professionalism, dedication, and competence in the performance of assigned duties and responsibilities.
    </p>

    <p>
      As a member of our workforce, {{ $employee->name }} has been entrusted with various tasks vital to the operations of the company, contributing significantly to team productivity and operational efficiency. He/She has shown a strong commitment to upholding company policies and standards throughout the duration of employment.
    </p>

    <p>
      This certification is issued upon the request of the aforementioned employee for whatever lawful purpose it may serve him/her best. We wish him/her continued success in all future endeavors.
    </p>
  </div>

  <div class="footer">
    <p>
      Given this <strong>{{ now()->format('jS') }}</strong> of
      <strong>{{ now()->format('F, Y') }}</strong>.
    </p>

    <div class="signature">
      <div class="signature-line"></div>
      <div>Moises A. Galicha</div>
      <div>Plant Manager</div>
    </div>
  </div>
</body>
</html>
