<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('page_title','ASIATEX HRTrack')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  @stack('styles')
</head>
<body class="d-flex flex-column" style="min-height:100vh;">
  <div class="d-flex flex-grow-1">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
      @include('partials.sidebar')
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="flex-grow-1 d-flex flex-column">
      @include('partials.navbar')

      {{-- IMPORTANT: remove overflow-y:auto; use .app-content to pad for fixed footer --}}
      <main class="app-content p-4" style="background:#f8f9fa;">
        @yield('content')
      </main>

      {{-- Fixed footer (centered) --}}
      <footer class="app-footer bg-white border-top">
        &copy; {{ date('Y') }} ASIATEX HRTrack. All rights reserved.
      </footer>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  @stack('modals')
  @stack('scripts')
</body>
</html>
