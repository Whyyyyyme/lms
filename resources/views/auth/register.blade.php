<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi Mahasiswa - EDU-TASK</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
        }

        html,
        body {
            margin: 0;
            min-height: 100vh;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 16% 18%, rgba(14, 165, 233, 0.22), transparent 30%),
                radial-gradient(circle at 88% 20%, rgba(37, 99, 235, 0.18), transparent 34%),
                radial-gradient(circle at 72% 90%, rgba(15, 23, 42, 0.12), transparent 34%),
                linear-gradient(135deg, #ffffff 0%, #f8fbff 34%, #eaf4ff 65%, #c7dcff 100%);
            overflow-x: hidden;
            position: relative;
            color: #0f172a;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 1;
            background-image:
                linear-gradient(rgba(15, 23, 42, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(circle at center, black 0%, transparent 78%);
        }

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 2;
            background:
                radial-gradient(circle at var(--cursor-x, 50%) var(--cursor-y, 50%), rgba(14, 165, 233, 0.18), transparent 30%),
                linear-gradient(90deg, rgba(255, 255, 255, 0.72), transparent 35%, transparent 72%, rgba(15, 23, 42, 0.08));
        }

        .page-orb {
            position: fixed;
            width: 440px;
            height: 440px;
            border-radius: 999px;
            pointer-events: none;
            z-index: 3;
            opacity: 0.34;
            filter: blur(58px);
            background: rgba(14, 165, 233, 0.30);
            transform: translate(-50%, -50%);
            left: var(--cursor-x, 50%);
            top: var(--cursor-y, 50%);
            transition: opacity 0.25s ease;
        }

        .register-page {
            position: relative;
            z-index: 4;
            width: 100%;
            min-height: 100vh;
            padding: 28px;
            perspective: 1300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-shell {
            width: 100%;
            max-width: 1240px;
            min-height: 720px;
            position: relative;
            border-radius: 34px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.82), rgba(239, 246, 255, 0.62)),
                radial-gradient(circle at top left, rgba(14, 165, 233, 0.16), transparent 34%),
                radial-gradient(circle at bottom right, rgba(30, 64, 175, 0.14), transparent 38%);
            border: 1px solid rgba(59, 130, 246, 0.22);
            box-shadow:
                0 36px 90px rgba(15, 23, 42, 0.22),
                inset 0 1px 0 rgba(255, 255, 255, 0.88);
            overflow: hidden;
            backdrop-filter: blur(20px) saturate(135%);
            transform-style: preserve-3d;
            transform:
                rotateX(var(--tilt-x, 0deg))
                rotateY(var(--tilt-y, 0deg))
                translate3d(var(--move-x, 0px), var(--move-y, 0px), 0);
            transition: transform 0.16s ease-out, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .register-shell.is-resetting {
            transition: transform 0.55s cubic-bezier(.2,.9,.25,1), box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .register-shell:hover {
            border-color: rgba(14, 165, 233, 0.42);
            box-shadow:
                0 42px 110px rgba(15, 23, 42, 0.26),
                0 0 70px rgba(14, 165, 233, 0.16),
                inset 0 1px 0 rgba(255, 255, 255, 0.92);
        }

        .register-shell::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(
                    circle at var(--card-x, 50%) var(--card-y, 50%),
                    rgba(14, 165, 233, 0.22),
                    rgba(37, 99, 235, 0.08) 26%,
                    transparent 48%
                );
            opacity: 0.9;
            z-index: 1;
        }

        .register-shell::after {
            content: "";
            position: absolute;
            inset: 1px;
            border-radius: 33px;
            pointer-events: none;
            z-index: 2;
            border: 1px solid rgba(255, 255, 255, 0.55);
            background:
                linear-gradient(120deg, rgba(255, 255, 255, 0.42), transparent 24%, transparent 78%, rgba(15, 23, 42, 0.03));
            mix-blend-mode: screen;
        }

        .register-grid {
            position: relative;
            z-index: 3;
            min-height: 720px;
            display: grid;
            grid-template-columns: 0.92fr 1.08fr;
        }

        .visual-panel {
            position: relative;
            padding: 34px;
            overflow: hidden;
            border-right: 1px solid rgba(59, 130, 246, 0.14);
            background: #ffffff;
            transform: translateZ(34px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .visual-panel::before {
            content: "";
            position: absolute;
            inset: 24px;
            border-radius: 28px;
            border: 1px solid rgba(14, 165, 233, 0.12);
            pointer-events: none;
            background: #ffffff;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 1),
                0 18px 45px rgba(15, 23, 42, 0.04);
        }

        .visual-panel::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 22px;
            width: 74%;
            height: 90px;
            transform: translateX(-50%);
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.08);
            filter: blur(38px);
            opacity: 0.45;
            pointer-events: none;
        }

        .rive-promo {
            position: relative;
            z-index: 5;
            width: min(118%, 760px);
            height: 610px;
            pointer-events: none;
            transform: translateZ(44px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border-radius: 22px;
            overflow: hidden;
        }

        .rive-promo canvas {
            width: 100%;
            height: 100%;
            display: block;
            background: #ffffff;
            transform: scale(1.23);
            transform-origin: center;
            filter: drop-shadow(0 20px 28px rgba(15, 23, 42, 0.08));
        }

        .rive-message {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 84%;
            transform: translate(-50%, -50%);
            color: rgba(30, 41, 59, 0.62);
            text-align: center;
            font-size: 13px;
            line-height: 1.5;
        }

        .form-panel {
            position: relative;
            padding: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: translateZ(62px);
            background:
                radial-gradient(circle at 28% 38%, rgba(14, 165, 233, 0.14), transparent 38%),
                linear-gradient(145deg, rgba(226, 239, 255, 0.42), rgba(255, 255, 255, 0.28));
        }

        .form-card {
            width: 100%;
            max-width: 650px;
            position: relative;
            border-radius: 30px;
            padding: 32px;
            background:
                linear-gradient(145deg, rgba(255, 255, 255, 0.90), rgba(239, 246, 255, 0.78)),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.13), transparent 42%);
            border: 1px solid rgba(59, 130, 246, 0.20);
            box-shadow:
                0 30px 80px rgba(15, 23, 42, 0.18),
                inset 0 1px 0 rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(24px) saturate(140%);
            overflow: hidden;
        }

        .form-card::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(
                    circle at var(--card-x, 50%) var(--card-y, 50%),
                    rgba(14, 165, 233, 0.16),
                    transparent 44%
                );
        }

        .form-content {
            position: relative;
            z-index: 2;
        }

        .form-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .brand h2 {
            margin: 0;
            color: #0f172a;
            font-size: 32px;
            font-weight: 950;
            letter-spacing: -0.04em;
        }

        .brand h2 span {
            color: #ef4444;
        }

        .brand p {
            margin: 10px 0 0;
            color: rgba(51, 65, 85, 0.76);
            font-size: 14px;
            line-height: 1.6;
        }

        .step-badge {
            flex: 0 0 auto;
            border: 1px solid rgba(14, 165, 233, 0.24);
            background: rgba(239, 246, 255, 0.78);
            color: #1e3a8a;
            border-radius: 999px;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 950;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            white-space: nowrap;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }

        .alert {
            width: 100%;
            border-radius: 18px;
            padding: 13px 15px;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 18px;
            border: 1px solid transparent;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.10);
            color: #991b1b;
            border-color: rgba(248, 113, 113, 0.24);
        }

        .alert-error ul {
            margin: 0;
            padding-left: 18px;
        }

        .register-form {
            width: 100%;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-group {
            min-width: 0;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            color: #1e3a8a;
            font-size: 12px;
            font-weight: 950;
            margin-bottom: 9px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            height: 50px;
            border: 1px solid rgba(59, 130, 246, 0.22);
            outline: none;
            border-radius: 17px;
            background-color: rgba(255, 255, 255, 0.82);
            color: #0f172a;
            padding: 0 16px;
            font-size: 14px;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.82),
                0 12px 30px rgba(15, 23, 42, 0.08);
            transition: 0.2s ease;
        }

        .form-group select {
            cursor: pointer;
            appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, #0ea5e9 50%),
                linear-gradient(135deg, #0ea5e9 50%, transparent 50%);
            background-position:
                calc(100% - 20px) 21px,
                calc(100% - 14px) 21px;
            background-size:
                6px 6px,
                6px 6px;
            background-repeat: no-repeat;
            padding-right: 42px;
        }

        .form-group select option {
            background: #ffffff;
            color: #0f172a;
        }

        .form-group input::placeholder {
            color: rgba(100, 116, 139, 0.78);
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: rgba(14, 165, 233, 0.72);
            background-color: rgba(255, 255, 255, 0.96);
            box-shadow:
                0 0 0 4px rgba(14, 165, 233, 0.13),
                0 18px 36px rgba(15, 23, 42, 0.11);
        }

        .help-text {
            margin: 7px 0 0;
            color: rgba(51, 65, 85, 0.66);
            font-size: 11px;
            line-height: 1.5;
        }

        .btn-register {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 18px;
            font-size: 15px;
            font-weight: 950;
            cursor: pointer;
            transition: 0.22s ease;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #ffffff;
            box-shadow:
                0 18px 38px rgba(37, 99, 235, 0.24),
                0 0 34px rgba(14, 165, 233, 0.14);
            margin-top: 18px;
        }

        .btn-register::before {
            content: "";
            position: absolute;
            top: 0;
            left: -110%;
            width: 90%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.30), transparent);
            transform: skewX(-18deg);
            transition: 0.55s ease;
        }

        .btn-register:hover::before {
            left: 120%;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow:
                0 24px 46px rgba(37, 99, 235, 0.30),
                0 0 44px rgba(14, 165, 233, 0.18);
        }

        .bottom-login {
            margin-top: 18px;
            text-align: center;
            color: rgba(51, 65, 85, 0.72);
            font-size: 13px;
        }

        .bottom-login a {
            color: #0ea5e9;
            font-weight: 950;
            text-decoration: none;
        }

        .bottom-login a:hover {
            text-decoration: underline;
        }

        .note-box {
            margin-top: 18px;
            border-radius: 18px;
            padding: 14px 15px;
            background: rgba(245, 158, 11, 0.10);
            border: 1px solid rgba(245, 158, 11, 0.24);
            color: #92400e;
            font-size: 12px;
            line-height: 1.6;
        }

        .note-box strong {
            display: block;
            margin-bottom: 3px;
            color: #78350f;
        }

        @media (max-width: 1100px) {
            .register-page {
                padding: 18px;
                align-items: flex-start;
            }

            .register-shell,
            .register-grid {
                min-height: auto;
            }

            .register-grid {
                grid-template-columns: 1fr;
            }

            .visual-panel {
                min-height: 560px;
                border-right: 0;
                border-bottom: 1px solid rgba(59, 130, 246, 0.14);
            }

            .rive-promo {
                width: min(112%, 660px);
                height: 520px;
            }

            .form-panel {
                padding: 34px;
            }
        }

        @media (max-width: 720px) {
            .register-page {
                padding: 12px;
            }

            .register-shell {
                border-radius: 24px;
                transform: none !important;
            }

            .register-shell::after {
                border-radius: 23px;
            }

            .visual-panel {
                padding: 20px;
                min-height: 440px;
            }

            .visual-panel::before {
                inset: 14px;
                border-radius: 22px;
            }

            .rive-promo {
                width: min(115%, 440px);
                height: 380px;
            }

            .rive-promo canvas {
                transform: scale(1.15);
            }

            .form-panel {
                padding: 18px;
            }

            .form-card {
                padding: 24px;
                border-radius: 24px;
            }

            .form-header {
                display: block;
            }

            .step-badge {
                display: inline-flex;
                margin-top: 14px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .brand h2 {
                font-size: 30px;
            }
        }

        @media (max-width: 420px) {
            .visual-panel {
                min-height: 390px;
            }

            .rive-promo {
                width: min(120%, 390px);
                height: 330px;
            }
        }
    </style>
</head>

<body>
    <div class="page-orb" aria-hidden="true"></div>

    <main class="register-page">
        <section class="register-shell" id="tiltCard">
            <div class="register-grid">
                <aside class="visual-panel">
                    <div class="rive-promo">
                        <canvas id="promoRiveCanvas" width="920" height="720"></canvas>
                        <div class="rive-message" id="promoRiveLoading">
                            Memuat animasi...
                        </div>
                    </div>
                </aside>

                <section class="form-panel">
                    <div class="form-card">
                        <div class="form-content">
                            <div class="form-header">
                                <div class="brand">
                                    <h2>EDU-<span>TASK</span></h2>
                                    <p>Registrasi akun mahasiswa LMS Praktikum.</p>
                                </div>

                                <div class="step-badge">
                                    Register Mahasiswa
                                </div>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-error">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register.store') }}" class="register-form">
                                @csrf

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="name">Nama Lengkap</label>
                                        <input
                                            id="name"
                                            type="text"
                                            name="name"
                                            value="{{ old('name') }}"
                                            required
                                            autofocus
                                            placeholder="Masukkan nama lengkap"
                                        >
                                    </div>

                                    <div class="form-group">
                                        <label for="nim_nip">NIM</label>
                                        <input
                                            id="nim_nip"
                                            type="text"
                                            name="nim_nip"
                                            value="{{ old('nim_nip') }}"
                                            required
                                            placeholder="Masukkan NIM"
                                        >
                                    </div>

                                    <div class="form-group full">
                                        <label for="email">Email Aktif</label>
                                        <input
                                            id="email"
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                            required
                                            autocomplete="username"
                                            placeholder="contoh: nama@gmail.com"
                                        >
                                        <p class="help-text">
                                            Gunakan email yang benar-benar bisa dibuka. Email ini akan dipakai untuk notifikasi LMS.
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <label for="study_semester_id">Semester Mahasiswa</label>
                                        <select
                                            id="study_semester_id"
                                            name="study_semester_id"
                                            required
                                        >
                                            <option value="">Pilih semester</option>
                                            @foreach ($studySemesters as $semester)
                                                <option value="{{ $semester->id }}" @selected((string) old('study_semester_id') === (string) $semester->id)>
                                                    {{ $semester->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="student_group">Kelas/Rombel</label>
                                        <select
                                            id="student_group"
                                            name="student_group"
                                            required
                                        >
                                            <option value="">Pilih kelas/rombel</option>
                                            @foreach ($studentGroups as $group)
                                                <option value="{{ $group }}" @selected(old('student_group') === $group)>
                                                    Kelas {{ $group }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="help-text">
                                            Pilih kelas/rombel sesuai kelas perkuliahan kamu, misalnya A, B, C, sampai H.
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Password LMS</label>
                                        <input
                                            id="password"
                                            type="password"
                                            name="password"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Minimal 8 karakter"
                                        >
                                        <p class="help-text">
                                            Password ini khusus untuk login LMS, bukan password Gmail.
                                        </p>
                                    </div>

                                    <div class="form-group">
                                        <label for="password_confirmation">Konfirmasi Password</label>
                                        <input
                                            id="password_confirmation"
                                            type="password"
                                            name="password_confirmation"
                                            required
                                            autocomplete="new-password"
                                            placeholder="Ulangi password LMS"
                                        >
                                    </div>
                                </div>

                                <button type="submit" class="btn-register">
                                    Daftar Mahasiswa
                                </button>
                            </form>

                            <div class="bottom-login">
                                Sudah punya akun?
                                <a href="{{ route('login') }}">Login di sini</a>
                            </div>

                            <div class="note-box">
                                <strong>Catatan:</strong>
                                Setelah register, akun belum langsung aktif. Admin harus memverifikasi akun terlebih dahulu.
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </main>

    <script src="https://unpkg.com/@rive-app/canvas@2.31.2"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.documentElement;
            const tiltCard = document.getElementById('tiltCard');

            let latestMouse = null;
            let frame = null;

            function clamp(value, min, max) {
                return Math.min(Math.max(value, min), max);
            }

            function updateCursorEffects(event) {
                const viewportWidth = window.innerWidth || document.documentElement.clientWidth;
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight;

                const cursorX = clamp(event.clientX / viewportWidth, 0, 1);
                const cursorY = clamp(event.clientY / viewportHeight, 0, 1);

                root.style.setProperty('--cursor-x', `${cursorX * 100}%`);
                root.style.setProperty('--cursor-y', `${cursorY * 100}%`);

                if (!tiltCard || window.innerWidth <= 720) {
                    return;
                }

                const rect = tiltCard.getBoundingClientRect();

                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                const cardX = clamp((x / rect.width) * 100, 0, 100);
                const cardY = clamp((y / rect.height) * 100, 0, 100);

                const centerX = (cardX - 50) / 50;
                const centerY = (cardY - 50) / 50;

                const rotateY = centerX * 6;
                const rotateX = centerY * -6;
                const moveX = centerX * 6;
                const moveY = centerY * 6;

                tiltCard.classList.remove('is-resetting');
                tiltCard.style.setProperty('--card-x', `${cardX}%`);
                tiltCard.style.setProperty('--card-y', `${cardY}%`);
                tiltCard.style.setProperty('--tilt-x', `${rotateX}deg`);
                tiltCard.style.setProperty('--tilt-y', `${rotateY}deg`);
                tiltCard.style.setProperty('--move-x', `${moveX}px`);
                tiltCard.style.setProperty('--move-y', `${moveY}px`);
            }

            document.addEventListener('mousemove', function (event) {
                latestMouse = event;

                if (frame) {
                    return;
                }

                frame = requestAnimationFrame(function () {
                    updateCursorEffects(latestMouse);
                    frame = null;
                });
            }, { passive: true });

            document.addEventListener('mouseleave', function () {
                if (!tiltCard) {
                    return;
                }

                tiltCard.classList.add('is-resetting');
                tiltCard.style.setProperty('--card-x', '50%');
                tiltCard.style.setProperty('--card-y', '50%');
                tiltCard.style.setProperty('--tilt-x', '0deg');
                tiltCard.style.setProperty('--tilt-y', '0deg');
                tiltCard.style.setProperty('--move-x', '0px');
                tiltCard.style.setProperty('--move-y', '0px');
            });

            if (typeof rive === 'undefined') {
                console.error('Library Rive gagal dimuat. Pastikan internet aktif.');
                return;
            }

            const promoCanvas = document.getElementById('promoRiveCanvas');
            const promoLoading = document.getElementById('promoRiveLoading');
            const promoRiveFile = "{{ asset('rive/8413-16141-startup-business-promotion.riv') }}";

            let promoRiveAnimation = null;

            if (promoCanvas) {
                promoRiveAnimation = new rive.Rive({
                    src: promoRiveFile,
                    canvas: promoCanvas,
                    autoplay: true,

                    layout: new rive.Layout({
                        fit: rive.Fit.Contain,
                        alignment: rive.Alignment.Center
                    }),

                    onLoad: function () {
                        if (promoLoading) {
                            promoLoading.style.display = 'none';
                        }

                        promoRiveAnimation.resizeDrawingSurfaceToCanvas();

                        const animations = promoRiveAnimation.animationNames || [];
                        const stateMachines = promoRiveAnimation.stateMachineNames || [];

                        if (stateMachines.length > 0) {
                            promoRiveAnimation.play(stateMachines[0]);
                        } else if (animations.length > 0) {
                            promoRiveAnimation.play(animations[0]);
                        }
                    },

                    onLoadError: function () {
                        if (promoLoading) {
                            promoLoading.innerHTML = 'Animasi belum tampil.<br>Pastikan file Rive ada di folder <b>public/rive</b>.';
                        }

                        console.error('Rive promo gagal dimuat. Cek file: public/rive/8413-16141-startup-business-promotion.riv');
                    }
                });
            }

            window.addEventListener('resize', function () {
                if (promoRiveAnimation) {
                    promoRiveAnimation.resizeDrawingSurfaceToCanvas();
                }
            });
        });
    </script>
</body>
</html>