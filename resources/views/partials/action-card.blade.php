<a href="{{ $href }}" class="action-card" style="display:block;padding:18px;">
    <div style="font-size:26px;margin-bottom:8px;">{{ $icon ?? '📌' }}</div>
    <div style="font-weight:900;font-size:18px;margin-bottom:6px;">{{ $title }}</div>
    <p style="margin:0 0 12px;color:var(--muted);line-height:1.45;">{{ $description ?? '' }}</p>
    <span style="font-weight:800;color:var(--primary);">Buka fitur →</span>
</a>
