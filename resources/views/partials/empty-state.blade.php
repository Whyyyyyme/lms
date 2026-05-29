<div class="card" style="padding:26px;text-align:center;color:var(--muted);">
    <div style="font-size:36px;margin-bottom:8px;">{{ $icon ?? '📭' }}</div>
    <div style="font-weight:800;color:var(--text);">{{ $title ?? 'Data belum ada' }}</div>
    @isset($description)
        <p style="margin:8px 0 0;">{{ $description }}</p>
    @endisset
</div>
