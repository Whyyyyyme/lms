<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EDU-TASK</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
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

        .login-page {
            position: relative;
            z-index: 4;
            width: 100%;
            max-width: 1180px;
            min-height: 680px;
            perspective: 1300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-shell {
            width: 100%;
            min-height: 640px;
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

        .login-shell.is-resetting {
            transition: transform 0.55s cubic-bezier(.2,.9,.25,1), box-shadow 0.35s ease, border-color 0.35s ease;
        }

        .login-shell:hover {
            border-color: rgba(14, 165, 233, 0.42);
            box-shadow:
                0 42px 110px rgba(15, 23, 42, 0.26),
                0 0 70px rgba(14, 165, 233, 0.16),
                inset 0 1px 0 rgba(255, 255, 255, 0.92);
        }

        .login-shell::before {
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

        .login-shell::after {
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

        .login-grid {
            position: relative;
            z-index: 3;
            min-height: 640px;
            display: grid;
            grid-template-columns: 1.04fr 0.96fr;
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
            padding: 52px;
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
            max-width: 440px;
            position: relative;
            border-radius: 30px;
            padding: 36px;
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

        .brand {
            margin-bottom: 28px;
        }

        .brand h2 {
            margin: 0;
            color: #0f172a;
            font-size: 34px;
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

        .alert {
            width: 100%;
            border-radius: 18px;
            padding: 13px 15px;
            font-size: 13px;
            line-height: 1.5;
            margin-bottom: 18px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: rgba(22, 163, 74, 0.11);
            color: #166534;
            border-color: rgba(34, 197, 94, 0.24);
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

        .form-box {
            width: 100%;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #1e3a8a;
            font-size: 12px;
            font-weight: 950;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.11em;
        }

        .form-group input {
            width: 100%;
            height: 54px;
            border: 1px solid rgba(59, 130, 246, 0.22);
            outline: none;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.82);
            color: #0f172a;
            padding: 0 18px;
            font-size: 15px;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.82),
                0 12px 30px rgba(15, 23, 42, 0.08);
            transition: 0.2s ease;
        }

        .form-group input::placeholder {
            color: rgba(100, 116, 139, 0.78);
        }

        .form-group input:focus {
            border-color: rgba(14, 165, 233, 0.72);
            background: rgba(255, 255, 255, 0.96);
            box-shadow:
                0 0 0 4px rgba(14, 165, 233, 0.13),
                0 18px 36px rgba(15, 23, 42, 0.11);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 4px 0 24px;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: rgba(51, 65, 85, 0.78);
            font-size: 13px;
            user-select: none;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: #0ea5e9;
        }

        .forgot-row a {
            color: #0ea5e9;
            font-size: 13px;
            font-weight: 900;
            text-decoration: none;
        }

        .forgot-row a:hover {
            text-decoration: underline;
        }

        .btn-login,
        .btn-register {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 18px;
            font-size: 15px;
            font-weight: 950;
            cursor: pointer;
            transition: 0.22s ease;
        }

        .btn-login {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #ffffff;
            box-shadow:
                0 18px 38px rgba(37, 99, 235, 0.24),
                0 0 34px rgba(14, 165, 233, 0.14);
            margin-bottom: 14px;
        }

        .btn-login::before {
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

        .btn-login:hover::before {
            left: 120%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow:
                0 24px 46px rgba(37, 99, 235, 0.30),
                0 0 44px rgba(14, 165, 233, 0.18);
        }

        .btn-register {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.74);
            color: #1e3a8a;
            border: 1px solid rgba(59, 130, 246, 0.22);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
        }

        .btn-register:hover {
            border-color: rgba(14, 165, 233, 0.58);
            background: rgba(239, 246, 255, 0.94);
            transform: translateY(-2px);
        }

        .security-note {
            margin-top: 18px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: rgba(51, 65, 85, 0.72);
            font-size: 12px;
            line-height: 1.6;
        }

        .security-note span {
            width: 24px;
            height: 24px;
            flex: 0 0 24px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.22);
            color: #0284c7;
            font-weight: 950;
        }

        @media (max-width: 1024px) {
            body {
                padding: 18px;
            }

            .login-page,
            .login-shell,
            .login-grid {
                min-height: auto;
            }

            .login-grid {
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

        @media (max-width: 640px) {
            body {
                padding: 12px;
                align-items: flex-start;
            }

            .login-shell {
                border-radius: 24px;
                transform: none !important;
            }

            .login-shell::after {
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

            .form-options {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
            }

            .brand h2 {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>
    <div class="page-orb" aria-hidden="true"></div>

    <main class="login-page">
        <section class="login-shell" id="tiltCard">
            <div class="login-grid">
                <div class="visual-panel">
                    <div class="rive-promo">
                        <canvas id="promoRiveCanvas" width="920" height="720"></canvas>
                        <div class="rive-message" id="promoRiveLoading">
                            Memuat animasi...
                        </div>
                    </div>
                </div>

                <div class="form-panel">
                    <div class="form-card">
                        <div class="form-content">
                            <div class="brand">
                                <h2>EDU-<span>TASK</span></h2>
                                <p>Silakan masuk menggunakan akun yang sudah terdaftar.</p>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-error">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.store') }}" class="form-box">
                                @csrf

                                <div class="form-group">
                                    <label for="email">Email ID</label>
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        placeholder="Masukkan email"
                                    >
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Masukkan password"
                                    >
                                </div>

                                <div class="form-options">
                                    <label class="remember" for="remember">
                                        <input
                                            id="remember"
                                            type="checkbox"
                                            name="remember"
                                            value="1"
                                            {{ old('remember') ? 'checked' : '' }}
                                        >
                                        <span>Ingat saya</span>
                                    </label>

                                    <div class="forgot-row">
                                        <a href="#">Forgot Password?</a>
                                    </div>
                                </div>

                                <button type="submit" class="btn-login">
                                    Log In
                                </button>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn-register">
                                        Register
                                    </a>
                                @else
                                    <a href="{{ url('/register') }}" class="btn-register">
                                        Register
                                    </a>
                                @endif
                            </form>

                            <div class="security-note">
                                <span>i</span>
                                <p>
                                    Akses akun disesuaikan dengan role pengguna:
                                    Admin, Asisten Praktikum, atau Mahasiswa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
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

                if (!tiltCard || window.innerWidth <= 640) {
                    return;
                }

                const rect = tiltCard.getBoundingClientRect();

                const x = event.clientX - rect.left;
                const y = event.clientY - rect.top;

                const cardX = clamp((x / rect.width) * 100, 0, 100);
                const cardY = clamp((y / rect.height) * 100, 0, 100);

                const centerX = (cardX - 50) / 50;
                const centerY = (cardY - 50) / 50;

                const rotateY = centerX * 7;
                const rotateX = centerY * -7;
                const moveX = centerX * 7;
                const moveY = centerY * 7;

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
