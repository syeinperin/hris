<div class="bg-dark text-white p-3 vh-100" style="width: 250px;">
    <h4 class="fw-bold text-white mb-4">ASIATEX</h4>
    <ul class="nav flex-column">
        @foreach(\App\Models\Sidebar::whereNull('parent_id')->orderBy('order')->get() as $item)
            <li class="nav-item">
                @php
                    $subItems = \App\Models\Sidebar::where('parent_id', $item->id)->orderBy('order')->get();
                @endphp
                
                @if ($subItems->count() > 0)
                    <!-- Parent Menu with Dropdown -->
                    <a class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" href="#menu-{{ $item->id }}" role="button">
                        <i class="ph ph-{{ $item->icon }} fs-5 me-2"></i> {{ $item->name }}
                    </a>
                    <ul class="collapse list-unstyled ms-3" id="menu-{{ $item->id }}">
                        @foreach ($subItems as $subItem)
                            <li>
                                <a class="nav-link text-white" href="{{ route($subItem->route) }}">
                                    <i class="ph ph-{{ $subItem->icon }} fs-5 me-2"></i> {{ $subItem->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <!-- Regular Menu Item -->
                    <a class="nav-link text-white d-flex align-items-center" href="{{ route($item->route) }}">
                        <i class="ph ph-{{ $item->icon }} fs-5 me-2"></i> {{ $item->name }}
                    </a>
                @endif
            </li>
        @endforeach
    </ul>
</div>
