@extends('layouts.app')

@section('title', 'Jadwal Praktikum')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $previousQuery = array_filter([
        'bulan' => $previousMonth->format('Y-m'),
        'mata_kuliah' => $selectedCourseId,
    ]);

    $nextQuery = array_filter([
        'bulan' => $nextMonth->format('Y-m'),
        'mata_kuliah' => $selectedCourseId,
    ]);

    $todayQuery = array_filter([
        'bulan' => now()->format('Y-m'),
        'mata_kuliah' => $selectedCourseId,
    ]);

    $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    $dashboardUrl = Route::has('student.dashboard')
        ? route('student.dashboard')
        : '#';

    $coursesUrl = Route::has('student.courses.index')
        ? route('student.courses.index')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Jadwal Praktikum</h1>

    <p>
        Kalender aktivitas praktikum, deadline tugas, dan sesi absensi.
        Gunakan filter mata kuliah untuk melihat jadwal pada kelas tertentu.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        @if(Route::has('student.courses.index'))
            <a href="{{ $coursesUrl }}" class="btn btn-primary">
                📚 Mata Kuliah Saya
            </a>
        @endif
    </div>
</section>

<div class="schedule-layout">
    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">
                    {{ $month->translatedFormat('F Y') }}
                </h2>

                <div class="section-subtitle">
                    Praktikum, tugas, dan absensi ditampilkan dalam satu kalender.
                </div>
            </div>

            <div class="actions-inline">
                <form method="GET" action="{{ route('student.schedule.index') }}">
                    <input type="hidden" name="bulan" value="{{ $month->format('Y-m') }}">

                    <select
                        name="mata_kuliah"
                        onchange="this.form.submit()"
                        class="form-control"
                        style="width: 230px;"
                    >
                        <option value="">Semua mata kuliah</option>

                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) $selectedCourseId === (int) $course->id)>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <a href="{{ route('student.schedule.index', $previousQuery) }}" class="btn btn-sm">
                    ←
                </a>

                <a href="{{ route('student.schedule.index', $todayQuery) }}" class="btn btn-sm">
                    Hari ini
                </a>

                <a href="{{ route('student.schedule.index', $nextQuery) }}" class="btn btn-sm">
                    →
                </a>
            </div>
        </div>

        <div class="metric-row" style="margin-bottom: 18px;">
            <span class="metric-pill">
                🔵 Praktikum
            </span>

            <span class="metric-pill">
                🟠 Tugas
            </span>

            <span class="metric-pill">
                🟢 Absensi
            </span>
        </div>

        <div class="schedule-scroll">
            <div class="schedule-calendar">
                <div class="schedule-week-header">
                    @foreach($dayNames as $dayName)
                        <div class="schedule-day-name">
                            {{ $dayName }}
                        </div>
                    @endforeach
                </div>

                @foreach($weeks as $week)
                    <div class="schedule-week-row">
                        @foreach($week as $day)
                            <div class="schedule-day {{ $day['is_current_month'] ? '' : 'schedule-day-muted' }}">
                                <div class="schedule-day-top">
                                    <span class="schedule-date {{ $day['is_today'] ? 'schedule-date-today' : '' }}">
                                        {{ $day['date']->day }}
                                    </span>

                                    @if($day['events']->count() > 0)
                                        <span class="status-pill status-muted">
                                            {{ $day['events']->count() }}
                                        </span>
                                    @endif
                                </div>

                                <div class="schedule-events">
                                    @foreach($day['events']->take(4) as $event)
                                        @php
                                            $eventVariant = $event['variant'] ?? 'default';
                                        @endphp

                                        <a
                                            href="{{ $event['url'] ?: '#' }}"
                                            class="schedule-event schedule-event-{{ $eventVariant }}"
                                            data-tooltip="{{ $event['title'] }}{{ $event['time'] ? ' • '.$event['time'] : '' }} • {{ $event['badge'] }} • {{ $event['subtitle'] }}"
                                        >
                                            <div class="schedule-event-title">
                                                @if($event['time'])
                                                    <span>{{ $event['time'] }}</span>
                                                    <span> · </span>
                                                @endif

                                                <span>{{ $event['title'] }}</span>
                                            </div>

                                            <div class="schedule-event-meta">
                                                {{ $event['badge'] }} · {{ $event['subtitle'] }}
                                            </div>
                                        </a>
                                    @endforeach

                                    @if($day['events']->count() > 4)
                                        <div class="schedule-more">
                                            +{{ $day['events']->count() - 4 }} aktivitas lainnya
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <aside style="display: grid; gap: 18px; align-content: start;">
        <section class="card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Aktivitas Terdekat</h2>
                    <div class="section-subtitle">
                        Aktivitas praktikum, tugas, atau absensi yang akan datang.
                    </div>
                </div>
            </div>

            @if($upcomingEvents->isEmpty())
                <div class="empty-state">
                    <div style="font-size: 30px; margin-bottom: 8px;">🗓️</div>

                    <h3 class="empty-state-title">
                        Belum ada aktivitas
                    </h3>

                    <p class="empty-state-text">
                        Aktivitas praktikum, tugas, atau absensi akan muncul di sini.
                    </p>
                </div>
            @else
                <div class="list-stack">
                    @foreach($upcomingEvents as $event)
                        @php
                            $eventVariant = $event['variant'] ?? 'default';
                        @endphp

                        <a
                            href="{{ $event['url'] ?: '#' }}"
                            class="list-item"
                        >
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                                    <span class="status-pill schedule-pill-{{ $eventVariant }}">
                                        {{ $event['badge'] }}
                                    </span>

                                    <span class="status-pill status-muted">
                                        {{ Carbon::parse($event['date'])->format('d M') }}
                                    </span>
                                </div>

                                <h3 class="item-title">
                                    {{ $event['title'] }}
                                </h3>

                                <div class="item-meta">
                                    @if($event['time'])
                                        {{ $event['time'] }} ·
                                    @endif

                                    {{ $event['subtitle'] }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Catatan</h2>
                    <div class="section-subtitle">
                        Informasi format jadwal kelas praktikum.
                    </div>
                </div>
            </div>

            <p class="item-meta" style="margin-top: 0; line-height: 1.7;">
                Jadwal praktikum diambil dari data kelas. Agar muncul di kalender,
                format jadwal kelas sebaiknya berisi nama hari, misalnya:
            </p>

            <div
                style="
                    margin-top: 12px;
                    padding: 14px;
                    border-radius: 16px;
                    background: #f8fafc;
                    border: 1px solid var(--line);
                    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                    font-size: 13px;
                    color: #334155;
                "
            >
                Senin, 10:00-12:00
            </div>
        </section>
    </aside>
</div>

<style>
    .schedule-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 22px;
        align-items: start;
    }

    .schedule-scroll {
        width: 100%;
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .schedule-calendar {
        min-width: 900px;
        overflow: hidden;
        border: 1px solid var(--line);
        border-radius: 22px;
        background: #ffffff;
    }

    .schedule-week-header,
    .schedule-week-row {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
    }

    .schedule-week-row {
        border-top: 1px solid var(--line);
    }

    .schedule-day-name {
        padding: 13px;
        text-align: center;
        font-size: 13px;
        font-weight: 900;
        color: #334155;
        background: #f8fafc;
        border-right: 1px solid var(--line);
    }

    .schedule-day-name:last-child {
        border-right: 0;
    }

    .schedule-day {
        min-width: 0;
        min-height: 150px;
        padding: 12px;
        background: #ffffff;
        border-right: 1px solid var(--line);
        position: relative;
    }

    .schedule-day:last-child {
        border-right: 0;
    }

    .schedule-day-muted {
        background: #f8fafc;
        color: #94a3b8;
    }

    .schedule-day-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }

    .schedule-date {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 950;
        color: #334155;
    }

    .schedule-date-today {
        background: var(--primary);
        color: #ffffff;
    }

    .schedule-events {
        display: grid;
        gap: 7px;
        min-width: 0;
        max-width: 100%;
    }

    .schedule-event {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        padding: 8px 9px;
        border-radius: 12px;
        border: 1px solid var(--line);
        font-size: 11px;
        font-weight: 800;
        line-height: 1.35;
        text-decoration: none;
        overflow: hidden;
        transition:
            transform 0.18s ease,
            box-shadow 0.18s ease,
            border-color 0.18s ease,
            filter 0.18s ease;
    }

    .schedule-event:hover {
        transform: translateY(-2px) scale(1.015);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.10);
        filter: saturate(1.06);
        z-index: 5;
    }

    .schedule-event-title,
    .schedule-event-meta {
        display: block;
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .schedule-event-title span,
    .schedule-event-meta span {
        min-width: 0;
    }

    .schedule-event-meta {
        margin-top: 3px;
        font-size: 10px;
        opacity: 0.78;
    }

    .schedule-event-praktikum,
    .schedule-pill-praktikum {
        background: #e0f2fe;
        border-color: #bae6fd;
        color: #075985;
    }

    .schedule-event-tugas,
    .schedule-pill-tugas {
        background: #fef3c7;
        border-color: #fde68a;
        color: #92400e;
    }

    .schedule-event-absensi,
    .schedule-pill-absensi {
        background: #dcfce7;
        border-color: #bbf7d0;
        color: #166534;
    }

    .schedule-event-default,
    .schedule-pill-default {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #334155;
    }

    .schedule-more {
        padding: 7px 9px;
        border-radius: 12px;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (max-width: 1100px) {
        .schedule-layout {
            grid-template-columns: 1fr;
        }

        .section-header {
            display: block;
        }

        .section-header .actions-inline {
            margin-top: 12px;
        }
    }

        .calendar-hover-tooltip {
        position: fixed;
        z-index: 99999;
        max-width: 360px;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(15, 23, 42, 0.96);
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.45;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.28);
        pointer-events: none;
        opacity: 0;
        transform: translateY(8px) scale(0.96);
        transition:
            opacity 0.14s ease,
            transform 0.14s ease;
        white-space: normal;
    }

    .calendar-hover-tooltip.is-visible {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    .schedule-event:hover {
        transform: translateY(-2px) scale(1.025);
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.14);
        filter: saturate(1.08);
        z-index: 20;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tooltip = document.createElement('div');
        tooltip.className = 'calendar-hover-tooltip';
        document.body.appendChild(tooltip);

        function moveTooltip(event) {
            const offset = 14;
            const tooltipWidth = tooltip.offsetWidth || 320;
            const tooltipHeight = tooltip.offsetHeight || 60;

            let left = event.clientX + offset;
            let top = event.clientY + offset;

            if (left + tooltipWidth > window.innerWidth - 12) {
                left = event.clientX - tooltipWidth - offset;
            }

            if (top + tooltipHeight > window.innerHeight - 12) {
                top = event.clientY - tooltipHeight - offset;
            }

            tooltip.style.left = `${left}px`;
            tooltip.style.top = `${top}px`;
        }

        document.querySelectorAll('.schedule-event[data-tooltip]').forEach(function (eventCard) {
            eventCard.addEventListener('mouseenter', function (event) {
                tooltip.textContent = eventCard.dataset.tooltip;
                tooltip.classList.add('is-visible');
                moveTooltip(event);
            });

            eventCard.addEventListener('mousemove', function (event) {
                moveTooltip(event);
            });

            eventCard.addEventListener('mouseleave', function () {
                tooltip.classList.remove('is-visible');
            });
        });
    });
</script>

@endsection