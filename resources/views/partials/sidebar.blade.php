<div class="bg-dark text-white p-3 vh-100" style="width: 250px;">
    <h4 class="fw-bold text-white mb-4">ASIATEX</h4>
    <ul class="nav flex-column">
        @foreach(\App\Models\Sidebar::orderBy('order')->get() as $item)
            <li class="nav-item">
                <a class="nav-link text-white d-flex align-items-center" href="{{ route($item->route) }}">
                    <i class="ph ph-{{ $item->icon }} fs-5 me-2"></i> {{ $item->name }}
                </a>
            </li>
        @endforeach
    </ul>
</div>

