@props([
  'border'       => 'primary',   // Bootstrap border-* color
  'icon'         => 'info-circle', // Bootstrap Icons name
  'iconColor'    => null,         // optional text-* for icon
  'title'        => '',
  'value'        => '',
  'buttonText'   => '',
  'buttonRoute'  => '#',
  'buttonClass'  => null,        // overrides outline class
])

@php
  $iconClass = 'bi-' . $icon;
  $btnClass  = $buttonClass ?? "btn-outline-{$border}";
@endphp

<div class="col-md-3">
  <div class="card p-4 border-{{ $border }}">
    <div class="d-flex align-items-center">
      <i class="bi {{ $iconClass }} fs-1 text-{{ $border }}{{ $iconColor ? ' ' . $iconColor : '' }}"></i>
      <div class="ms-3">
        <h2 class="mb-0">{{ $value }}</h2>
        <div class="text-muted">{{ $title }}</div>
      </div>
    </div>
    @if($buttonText)
      <a href="{{ $buttonRoute }}"
         class="btn {{ $btnClass }} mt-3">
        {{ $buttonText }}
      </a>
    @endif
  </div>
</div>
