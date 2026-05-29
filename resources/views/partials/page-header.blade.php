<div class="page-header">
    <div>
        @isset($eyebrow)
            <div style="font-size:12px;font-weight:800;color:var(--primary);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">{{ $eyebrow }}</div>
        @endisset
        <h1 class="page-title">{{ $title ?? 'Halaman' }}</h1>
        @isset($description)
            <p class="page-description">{{ $description }}</p>
        @endisset
    </div>
    @isset($action)
        <div>{{ $action }}</div>
    @endisset
</div>
