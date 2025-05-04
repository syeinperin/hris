<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','Asiatex HRIS')</title>

  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >

  <style>
    /* full-screen background cover */
    body.auth-bg {
      background: url('{{ asset("images/bg.jpg") }}') no-repeat center center fixed;
      background-size: cover;
      margin: 0;
    }
    /* white overlay so text stands out */
    .auth-overlay {
      background: rgba(255,255,255,0.85);
      min-height: 100vh;
    }
  </style>
</head>
<body class="auth-bg">

  <div class="auth-overlay d-flex justify-content-center align-items-center">
    <div class="container">@yield('content')</div>
  </div>

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
