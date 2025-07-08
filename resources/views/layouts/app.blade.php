<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('page_title', 'Asiatex HRIS')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  <style>
    html, body { height:100%; margin:0; }
    .app-container { display:flex; height:100vh; }
    .sidebar { width:240px; background:#212529; color:#fff; padding:1rem; overflow-y:auto; }
    .main-wrapper { flex:1; display:flex; flex-direction:column; }
    .navbar { flex-shrink:0; z-index:1020; }
    .main-content { flex:1; overflow-y:auto; background:#f8f9fa; padding:1rem; }
    footer { flex-shrink:0; }
  </style>
  @stack('styles')
</head>
<body class="d-flex flex-column h-100">
  <div class="app-container">
    <aside class="sidebar">@include('partials.sidebar')</aside>
    <div class="main-wrapper">
      @include('partials.navbar')
      <main class="main-content">@yield('content')</main>
      <footer class="bg-white text-center py-3 border-top">
        &copy; {{ date('Y') }} Asiatex HRIS. All rights reserved.
      </footer>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/app.js') }}"></script>
  @stack('scripts')
  {{-- HERE: render any pushed modals --}}
  @stack('modals')
</body>
</html>
