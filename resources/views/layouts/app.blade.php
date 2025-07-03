{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('page_title', 'Asiatex HRIS')</title>

  {{-- CSRF token for AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- App CSS -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

  <style>
    /* Full-height container, no native scrollbars */
    html, body { height: 100%; margin: 0; overflow: hidden; }

    /* Top-level flex holds sidebar + main */
    .app-container {
      display: flex;
      height: 100%;
    }

    /* Sidebar */
    .sidebar {
      flex-shrink: 0;
      width: 240px;
      height: 100vh;
      position: sticky;
      top: 0;
      background: #212529;
      color: #fff;
      padding: 1rem;
      overflow-y: auto;
    }

    /* Main wrapper for navbar + content + footer */
    .main-wrapper {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
    }

    /* Navbar stays visible */
    .navbar {
      flex-shrink: 0;
      z-index: 1020;
    }

    /* Scrollable main content */
    .main-content {
      flex: 1 1 auto;
      overflow-y: auto;
      background: #f8f9fa;
      padding: 1rem;
    }

    footer {
      flex-shrink: 0;
    }
  </style>
  @stack('styles')
</head>
<body class="d-flex flex-column h-100">

  <div class="app-container">

    {{-- Sidebar --}}
    <aside class="sidebar">
      @include('partials.sidebar')
    </aside>

    {{-- Main area --}}
    <div class="main-wrapper">
      @include('partials.navbar')

      <main class="main-content">
        @yield('content')
      </main>

      <footer class="bg-white text-center py-3 border-top">
        &copy; {{ date('Y') }} Asiatex HRIS. All rights reserved.
      </footer>
    </div>

  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Your app JS -->
  <script src="{{ asset('js/app.js') }}"></script>

  @stack('scripts')
</body>
</html>
