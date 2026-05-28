<div class="space-y-6" wire:poll.8s>
    @if ($flashMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $flashMessage }}
        </div>
    @endif

    @if ($mode === 'mahasiswa')
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Absensi Aktif</h2>
                    <p class="text-sm text-slate-500">Klik check-in saat asisten membuka sesi absensi.</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($activeAttendances as $attendance)
                    @php($record = $attendance->records->first())
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm text-indigo-700">{{ $attendance->kelas?->course?->name }}</p>
                                <h3 class="mt-1 font-semibold text-slate-900">{{ $attendance->kelas?->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Dibuka {{ $attendance->opened_at?->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Terbuka</span>
                        </div>

                        <div class="mt-4">
                            @if ($record?->status === 'hadir')
                                <div class="rounded-xl bg-white px-4 py-3 text-sm font-medium text-emerald-700">
                                    Kamu sudah check-in pada {{ $record->checked_at?->format('H:i') }}.
                                </div>
                            @else
                                <button
                                    type="button"
                                    wire:click="checkIn({{ $attendance->id }})"
                                    wire:loading.attr="disabled"
                                    class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                                >
                                    Check-in Sekarang
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                        Belum ada sesi absensi yang sedang dibuka.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Absensi</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Kelas</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status Sesi</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status Saya</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Check-in</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($attendances as $attendance)
                            @php($record = $attendance->records->first())
                            <tr>
                                <td class="px-4 py-3 text-slate-700">{{ $attendance->session_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $attendance->kelas?->name }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendance->is_open ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $attendance->is_open ? 'Terbuka' : 'Ditutup' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php($status = $record?->status ?? 'belum ada')
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($status === 'izin' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $record?->checked_at?->format('H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat absensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-900">Kelola Sesi Absensi</h2>
                <p class="text-sm text-slate-500">Buka atau tutup sesi absensi untuk kelas yang kamu asisteni.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($classes as $class)
                    <div class="rounded-2xl border border-slate-200 p-4">
                        <p class="text-sm text-slate-500">{{ $class->course?->name }}</p>
                        <h3 class="mt-1 font-semibold text-slate-900">{{ $class->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $class->room }} · {{ $class->schedule }}</p>

                        <div class="mt-4">
                            @if ($class->activeAttendance)
                                <button
                                    type="button"
                                    wire:click="closeSession({{ $class->activeAttendance->id }})"
                                    wire:confirm="Tutup sesi absensi kelas ini?"
                                    class="w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700"
                                >
                                    Tutup Absensi
                                </button>
                            @else
                                <button
                                    type="button"
                                    wire:click="openSession({{ $class->id }})"
                                    class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                                >
                                    Buka Absensi
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                        Kamu belum menjadi asisten pada kelas praktikum mana pun.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-1">
                <h2 class="text-lg font-semibold text-slate-900">Sesi Terbaru</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($attendances as $attendance)
                        <button
                            type="button"
                            wire:click="selectAttendance({{ $attendance->id }})"
                            class="w-full rounded-xl border px-4 py-3 text-left transition {{ $selectedAttendance?->id === $attendance->id ? 'border-indigo-300 bg-indigo-50' : 'border-slate-200 hover:bg-slate-50' }}"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $attendance->kelas?->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $attendance->session_date?->format('d/m/Y') }} · {{ $attendance->opened_at?->format('H:i') }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $attendance->is_open ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $attendance->is_open ? 'Terbuka' : 'Ditutup' }}
                                </span>
                            </div>

                            <div class="mt-3 flex gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-emerald-700">Hadir {{ $attendance->hadir_count }}</span>
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-amber-700">Izin {{ $attendance->izin_count }}</span>
                                <span class="rounded-full bg-red-100 px-2 py-1 text-red-700">Alpha {{ $attendance->alpha_count }}</span>
                            </div>
                        </button>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                            Belum ada sesi absensi.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm xl:col-span-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Rekap Mahasiswa</h2>
                        @if ($selectedAttendance)
                            <p class="text-sm text-slate-500">{{ $selectedAttendance->kelas?->name }} · {{ $selectedAttendance->session_date?->format('d/m/Y') }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Mahasiswa</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">NIM</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Check-in</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @if ($selectedAttendance)
                                @forelse ($selectedAttendance->records as $record)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-900">{{ $record->student?->name }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $record->student?->nim_nip }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $record->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' : ($record->status === 'izin' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">{{ $record->checked_at?->format('H:i') ?? '-' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" wire:click="updateStudentStatus({{ $selectedAttendance->id }}, {{ $record->student_id }}, 'hadir')" class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">Hadir</button>
                                                <button type="button" wire:click="updateStudentStatus({{ $selectedAttendance->id }}, {{ $record->student_id }}, 'izin')" class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-100">Izin</button>
                                                <button type="button" wire:click="updateStudentStatus({{ $selectedAttendance->id }}, {{ $record->student_id }}, 'alpha')" class="rounded-lg bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-100">Alpha</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data mahasiswa pada sesi ini.</td>
                                    </tr>
                                @endforelse
                            @else
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">Pilih sesi absensi terlebih dahulu.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</div>
