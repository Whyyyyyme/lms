@extends('layouts.app', ['title' => 'Notifikasi'])

@section('content')
    @include('partials.page-header', [
        'eyebrow' => 'Notifikasi',
        'title' => 'Pusat Notifikasi',
    ])

    <div class="mb-5 flex justify-end">
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            @method('PATCH')

            <button type="submit"
                class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Tandai Semua Dibaca
            </button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div
                class="rounded-3xl border bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md {{ $notification->read_at ? '' : 'ring-2 ring-indigo-100' }}">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                    <a href="{{ route('notifications.open', ['notification' => $notification->id]) }}" class="block min-w-0 flex-1">
                        <p class="font-bold text-slate-900">
                            {{ $notification->title ?? $notification->type }}
                        </p>

                        <p class="mt-1 text-sm text-slate-600">
                            {{ $notification->message }}
                        </p>

                        <p class="mt-2 text-xs text-slate-400">
                            {{ $notification->created_at?->format('d M Y H:i') }}
                        </p>
                    </a>

                    <div class="flex shrink-0 gap-2">
                        @unless ($notification->read_at)
                            <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                    class="rounded-xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
                                    Dibaca
                                </button>
                            </form>
                        @endunless

                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST"
                            onsubmit="return confirm('Hapus notifikasi ini?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            @include('partials.empty-state', [
                'title' => 'Tidak ada notifikasi',
            ])
        @endforelse
    </div>

    <div class="mt-5">
        {{ $notifications->links() }}
    </div>
@endsection
