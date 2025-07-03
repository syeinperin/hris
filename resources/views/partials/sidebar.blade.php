<nav class="sidebar bg-dark text-white">
  <a href="{{ route('dashboard') }}"
     class="navbar-brand text-white fw-bold mb-4 d-block ps-3">
    <i class="bi bi-building"></i> ASIATEX
  </a>

  <ul class="nav flex-column list-unstyled mb-auto">
    @foreach($menuItems as $item)
      @if($item->children->isNotEmpty())
        @php
          $id   = "menu-{$item->id}";
          $open = $item->children->pluck('route')
                   ->filter(fn($r)=> request()->routeIs("$r*"))
                   ->isNotEmpty();
        @endphp
        <li class="nav-item mb-1">
          <a href="#{{ $id }}"
             class="nav-link d-flex justify-content-between align-items-center text-white fw-bold px-3"
             data-bs-toggle="collapse"
             aria-expanded="{{ $open?'true':'false' }}"
             aria-controls="{{ $id }}">
            <span>
              @if($item->icon)<i class="bi bi-{{ $item->icon }} me-2"></i>@endif
              {{ $item->title }}
            </span>
            <i class="bi bi-chevron-down"></i>
          </a>
          <div class="collapse @if($open) show @endif" id="{{ $id }}">
            <ul class="btn-toggle-nav list-unstyled small ps-4">
              @foreach($item->children as $child)
                <li class="mb-1">
                  <a href="{{ route($child->route) }}"
                     class="nav-link text-white fw-bold @if(request()->routeIs("{$child->route}*")) active @endif">
                    @if($child->icon)<i class="bi bi-{{ $child->icon }} me-2"></i>@endif
                    {{ $child->title }}
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
        </li>
      @else
        <li class="nav-item mb-1">
          <a href="{{ route($item->route) }}"
             class="nav-link text-white fw-bold px-3
               @if(request()->routeIs("{$item->route}*")) active @endif">
            @if($item->icon)<i class="bi bi-{{ $item->icon }} me-2"></i>@endif
            {{ $item->title }}
          </a>
        </li>
      @endif
    @endforeach
  </ul>
</nav>
