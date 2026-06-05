@extends('layouts.app', ['title' => 'Notifikasi'])

@section('content')
    @include('partials.page-header', [
        'eyebrow' => 'Notifikasi',
        'title' => 'Pusat Notifikasi',
        'description' => 'Pantau informasi terbaru tentang materi, tugas, absensi, nilai, dan pengumuman praktikum.',
    ])

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-700">
            {{ $notifications->total() }} notifikasi ditemukan
        </div>

        @if ($notifications->total() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                @method('PATCH')

                <button type="submit"
                    class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
            @php
                $data = is_array($notification->getAttribute('display_data'))
                    ? $notification->getAttribute('display_data')
                    : (is_array($notification->data) ? $notification->data : []);

                $typeLabel = $data['type_label'] ?? 'LMS';
                $tone = $data['type_tone'] ?? 'slate';
                $contextLabel = $data['context_label'] ?? null;
                $courseName = $data['course_name'] ?? null;
                $courseCode = $data['course_code'] ?? null;
                $className = $data['class_name'] ?? null;
                $deadlineLabel = $data['deadline_label'] ?? null;
                $sessionDateLabel = $data['session_date_label'] ?? null;
                $openedAtLabel = $data['opened_at_label'] ?? null;
                $closedAtLabel = $data['closed_at_label'] ?? null;
                $scoreLabel = $data['score_label'] ?? null;

                $typeClasses = match ($tone) {
                    'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                    'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
                    'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
                    'blue' => 'bg-blue-50 text-blue-700 ring-blue-100',
                    'violet' => 'bg-violet-50 text-violet-700 ring-violet-100',
                    default => 'bg-slate-100 text-slate-700 ring-slate-200',
                };
            @endphp

            <div
                class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md {{ $notification->read_at ? '' : 'ring-2 ring-indigo-100' }}">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                    <a href="{{ route('notifications.open', ['notification' => $notification->id]) }}"
                        class="block min-w-0 flex-1">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $typeClasses }}">
                                {{ $typeLabel }}
                            </span>

                            @if (is_null($notification->read_at))
                                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-red-100">
                                    Baru
                                </span>
                            @endif
                        </div>

                        <p class="font-bold text-slate-900">
                            {{ $notification->title ?? $notification->type ?? 'Notifikasi LMS' }}
                        </p>

                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            {{ $notification->message ?? 'Ada informasi baru dari LMS Praktikum.' }}
                        </p>

                        @if (!empty($contextLabel) || !empty($courseName) || !empty($className) || !empty($deadlineLabel) || !empty($sessionDateLabel) || !empty($scoreLabel))
                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                @if (!empty($contextLabel))
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 font-bold text-indigo-700 ring-1 ring-indigo-100">
                                        {{ $contextLabel }}
                                    </span>
                                @elseif (!empty($courseName))
                                    <span class="rounded-full bg-indigo-50 px-3 py-1 font-bold text-indigo-700 ring-1 ring-indigo-100">
                                        Mata Kuliah:
                                        {{ !empty($courseCode) ? $courseCode . ' - ' : '' }}{{ $courseName }}
                                    </span>
                                @endif

                                @if (!empty($className) && empty($contextLabel))
                                    <span class="rounded-full bg-slate-100 px-3 py-1 font-bold text-slate-700 ring-1 ring-slate-200">
                                        Kelas: {{ $className }}
                                    </span>
                                @endif

                                @if (!empty($deadlineLabel))
                                    <span class="rounded-full bg-amber-50 px-3 py-1 font-bold text-amber-700 ring-1 ring-amber-100">
                                        Deadline: {{ $deadlineLabel }}
                                    </span>
                                @endif

                                @if (!empty($sessionDateLabel))
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 font-bold text-emerald-700 ring-1 ring-emerald-100">
                                        Tanggal Absensi: {{ $sessionDateLabel }}
                                    </span>
                                @endif

                                @if (!empty($openedAtLabel))
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 font-bold text-emerald-700 ring-1 ring-emerald-100">
                                        Dibuka: {{ $openedAtLabel }}
                                    </span>
                                @endif

                                @if (!empty($closedAtLabel))
                                    <span class="rounded-full bg-red-50 px-3 py-1 font-bold text-red-700 ring-1 ring-red-100">
                                        Ditutup: {{ $closedAtLabel }}
                                    </span>
                                @endif

                                @if (!empty($scoreLabel))
                                    <span class="rounded-full bg-blue-50 px-3 py-1 font-bold text-blue-700 ring-1 ring-blue-100">
                                        Nilai: {{ $scoreLabel }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <p class="mt-3 text-xs font-medium text-slate-400">
                            {{ $notification->created_at?->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i') }} WIB
                        </p>
                    </a>

                    <div class="flex shrink-0 flex-wrap gap-2 md:justify-end">
                        <a href="{{ route('notifications.open', ['notification' => $notification->id]) }}"
                            class="rounded-xl bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                            Buka
                        </a>

                        @unless ($notification->read_at)
                            <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                    class="rounded-xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                    Dibaca
                                </button>
                            </form>
                        @endunless

                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST"
                            onsubmit="return confirm('Hapus notifikasi ini?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="rounded-xl bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            @include('partials.empty-state', [
                'title' => 'Tidak ada notifikasi',
                'description' => 'Notifikasi materi, tugas, absensi, nilai, dan pengumuman akan muncul di halaman ini.',
            ])
        @endforelse
    </div>

    <div class="mt-5">
        {{ $notifications->links() }}
    </div>
@endsection
