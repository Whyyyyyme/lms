@if(($method ?? 'GET') === 'POST')
    <form action="{{ $href }}" method="POST">
        @csrf
        <button type="submit" class="sidebar-link sidebar-logout {{ ($active ?? false) ? 'active' : '' }}" data-sidebar-link>
            <span class="sidebar-icon">{{ $icon ?? '•' }}</span>
            <span>{{ $label }}</span>
        </button>
    </form>
@else
    <a href="{{ $href }}" class="sidebar-link {{ ($active ?? false) ? 'active' : '' }}" data-sidebar-link>
        <span class="sidebar-icon">{{ $icon ?? '•' }}</span>
        <span>{{ $label }}</span>
    </a>
@endif
