@props(['icon', 'route', 'label'])

<a href="{{ $route }}"
   class="d-flex align-items-center mb-3 text-white text-decoration-none px-2 py-1 rounded hover-bg"
   style="transition: background 0.2s;">
    <i class="bi {{ $icon }} me-2"></i>
    {{ $label }}
</a>
