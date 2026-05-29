@if (session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
        {{ session('error') }}
    </div>
@endif

@if (session('status'))
    <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">
        {{ session('status') }}
    </div>
@endif
