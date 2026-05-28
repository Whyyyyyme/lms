<div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
    <div>
        <p class="text-sm font-medium uppercase tracking-wide text-indigo-600">{{ $eyebrow ?? 'LMS Praktikum' }}</p>
        <h1 class="mt-1 text-2xl font-bold text-slate-950">{{ $title }}</h1>
        @isset($description)
            <p class="mt-2 max-w-3xl text-sm text-slate-600">{{ $description }}</p>
        @endisset
    </div>
    @isset($action)
        <div>{!! $action !!}</div>
    @endisset
</div>
