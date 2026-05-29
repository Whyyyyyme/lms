<div class="stat-card" style="padding:18px;">
    <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
        <div>
            <div style="font-size:13px;color:var(--muted);font-weight:700;">{{ $label }}</div>
            <div style="font-size:30px;font-weight:900;margin-top:6px;">{{ $value }}</div>
        </div>
        <div style="font-size:28px;">{{ $icon ?? '📌' }}</div>
    </div>
</div>
