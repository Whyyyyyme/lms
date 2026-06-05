<div class="relative" wire:poll.10s>
    <button type="button" wire:click="toggle" data-notification-dropdown-toggle
        class="relative rounded-full p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900"
        aria-label="Buka notifikasi">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        @if ($unreadCount > 0)
            <span
                class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-bold text-white">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 z-50 mt-3 w-96 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
            data-notification-dropdown-panel>
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                <div>
                    <h3 class="font-semibold text-slate-900">Notifikasi</h3>
                    <p class="text-xs text-slate-500">{{ $unreadCount }} belum dibaca</p>
                </div>

                @if ($unreadCount > 0)
                    <button type="button" wire:click="markAllAsRead"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                        Tandai semua dibaca
                    </button>
                @endif
            </div>

            <div class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
                @forelse ($notifications as $notification)
                    <div class="px-4 py-3 {{ $notification->read_at ? 'bg-white' : 'bg-indigo-50/60' }}">
                        <div class="flex items-start justify-between gap-3">
                            <a href="{{ route('notifications.open', ['notification' => $notification->id]) }}"
                                class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ $notification->title ?? 'Notifikasi LMS' }}
                                </p>
                                <p class="mt-1 line-clamp-2 text-sm text-slate-600">
                                    {{ $notification->message ?? data_get($notification->data, 'message', '-') }}
                                </p>

                                @php
                                    $courseCode = $data['course_code'] ?? null;
                                    $courseName = $data['course_name'] ?? null;
                                @endphp

                                @if (!empty($courseName))
                                    <p class="mt-1 text-[11px] font-bold text-indigo-600">
                                        {{ !empty($courseCode) ? $courseCode . ' - ' : '' }}{{ $courseName }}
                                    </p>
                                @endif

                                @if (!empty($data['class_name']))
                                    <p class="text-[11px] font-semibold text-slate-400">
                                        Kelas: {{ $data['class_name'] }}
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </p>
                            </a>

                            <div class="flex shrink-0 items-center gap-1">
                                @if (is_null($notification->read_at))
                                    <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                                        class="rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                                        Dibaca
                                    </button>
                                @endif

                                <button type="button" wire:click="deleteNotification('{{ $notification->id }}')"
                                    wire:confirm="Hapus notifikasi ini?"
                                    class="rounded-lg px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center text-sm text-slate-500">
                        Belum ada notifikasi.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3 text-center">
                <a href="{{ route('notifications.index') }}"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                    Lihat semua notifikasi
                </a>
            </div>
        </div>
    @endif
</div>
