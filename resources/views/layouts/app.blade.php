<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('page_title','ASIATEX HRTrack')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- 1) Poppins --}}
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  {{-- 2) Bootstrap CSS --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- 3) Bootstrap Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  {{-- 4) App CSS --}}
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

  {{-- === Page-specific CSS === --}}
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

      <main class="flex-fill p-4" style="background:#f8f9fa; overflow-y:auto;">
        @yield('content')
      </main>

      <footer class="bg-white text-center py-3 border-top">
        &copy; {{ date('Y') }} ASIATEX HRTrack. All rights reserved.
      </footer>
    </div>
  </div>

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

  {{-- === Modals pushed from child views === --}}
  @stack('modals')

  {{-- === Page-specific JS === --}}
  @stack('scripts')
</body>
</html>
