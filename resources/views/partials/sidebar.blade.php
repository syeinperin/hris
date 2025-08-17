{{-- resources/views/partials/sidebar.blade.php --}}
<nav class="nav flex-column">

  {{-- Brand / Logo --}}
  <a class="sidebar-brand" href="{{ route('dashboard') }}">
    <img src="{{ asset('images/asiatex-logo.png') }}" alt="ASIATEX Logo" class="brand-logo">
  
  </a>

  {{-- Dynamic menu items --}}
  @foreach($menuItems as $item)
    <a
      class="nav-link @if(request()->routeIs($item->route.'*')) active @endif"
      href="{{ route($item->route) }}"
    >
      @if($item->icon)
        <i class="bi bi-{{ $item->icon }} me-2"></i>
      @endif
      {{ $item->title }}
    </a>

    {{-- Sub-items --}}
    @if($item->children->isNotEmpty())
      @foreach($item->children as $child)
        <a
          class="nav-link ps-4 @if(request()->routeIs($child->route.'*')) active @endif"
          href="{{ route($child->route) }}"
        >
          @if($child->icon)
            <i class="bi bi-{{ $child->icon }} me-2"></i>
          @endif
          {{ $child->title }}
        </a>
      @endforeach
    @endif
  @endforeach

  {{-- Static Settings link --}}
  <div class="mt-4"></div>
  <a
    class="nav-link @if(request()->routeIs('settings*')) active @endif"
    href="{{ route('settings') }}"
  >
    <i class="bi bi-gear me-2"></i>
    Settings
  </a>
</nav>
