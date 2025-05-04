<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('page_title', 'Asiatex HRIS')</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Your custom CSS -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

  <style>
    /* Keep html/body full height, but allow overall page scrolling */
    html, body { height: 100%; margin: 0; overflow: auto; }
    /* Sidebar: sticky, full-height, scroll when too tall */
    .sidebar { width:240px; height:100vh; position:sticky; top:0; overflow-y:auto; padding:1rem; background:#212529; }
    /* Main flex container holds navbar + content + footer */
    .flex-grow-1.d-flex.flex-column { flex:1 1 auto; display:flex; flex-direction:column; min-height:100vh; }
    .navbar { flex-shrink:0; z-index:1020; }
    .main-content { flex:1 1 auto; overflow-y:auto; background:#f8f9fa; padding:1rem; }
    footer { flex-shrink:0; }
  </style>
</head>
<body class="d-flex flex-column h-100">

  <div class="d-flex flex-grow-1">

    {{-- Sidebar --}}
    @include('partials.sidebar')

    {{-- Main --}}
    <div class="flex-grow-1 d-flex flex-column">
      @include('partials.navbar')
      <main class="main-content">
        @yield('content')
      </main>
      <footer class="bg-white text-center py-3 border-top">
        &copy; {{ date('Y') }} Asiatex HRIS. All rights reserved.
      </footer>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- App’s compiled JS -->
  <script src="{{ asset('js/app.js') }}"></script>

  {{-- Page-specific scripts --}}
  @stack('scripts')  {{-- switched from @yield('scripts') :contentReference[oaicite:0]{index=0}:contentReference[oaicite:1]{index=1} --}}
</body>
</html>
