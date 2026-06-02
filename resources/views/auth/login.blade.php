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
            font-family: Arial, Helvetica, sans-serif;
        }

        html,
        body {
            margin: 0;
            min-height: 100vh;
        }

        body {
            background: #edf2f6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px;
            overflow-x: hidden;
            position: relative;
        }

        /* BACKGROUND RIVE PALING BELAKANG */
        .page-bg-rive {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
            opacity: 0.9;
        }

        #pageBgRiveCanvas {
            width: 100vw;
            height: 100vh;
            display: block;
            background: transparent;
        }

        /* LAYER TIPIS AGAR FORM TETAP TERBACA */
        .page-bg-overlay {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            background: rgba(237, 242, 246, 0.30);
            backdrop-filter: blur(2px);
        }

        .login-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 1220px;
            min-height: 700px;
            background: rgba(243, 247, 250, 0.72);
            backdrop-filter: blur(12px);
            border-radius: 36px;
            box-shadow: 0 18px 70px rgba(17, 24, 39, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 56px 70px;
        }

        .login-card {
            width: 100%;
            max-width: 1040px;
            min-height: 560px;
            background: rgba(248, 251, 253, 0.82);
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.20);
            display: grid;
            grid-template-columns: 430px 1fr;
            overflow: hidden;
        }

        .left-panel {
            background: #241875;
            position: relative;
            color: #ffffff;
            border-top-right-radius: 34px;
            border-bottom-right-radius: 34px;
            padding: 34px 30px 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        .left-panel::before {
            content: "";
            position: absolute;
            top: 38px;
            left: 35px;
            width: 96px;
            height: 96px;
            background-image: radial-gradient(rgba(255, 255, 255, 0.42) 1.4px, transparent 1.4px);
            background-size: 13px 13px;
            z-index: 1;
        }

        .rive-box {
            position: relative;
            z-index: 2;
            flex: 1;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 44px;
            margin-bottom: 28px;
        }

        .rive-stage {
            position: relative;
            width: 100%;
            height: 470px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        #loginRiveCanvas {
            width: 560px;
            height: 470px;
            display: block;
            background: transparent;
            transform: scale(1.22);
            transform-origin: center;
            mix-blend-mode: multiply;
        }

        .rive-loading,
        .rive-error {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 88%;
            text-align: center;
            font-size: 14px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.86);
        }

        .rive-error {
            display: none;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 14px;
            padding: 14px;
        }

        .bottom-line {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(90deg, #a9b4ff, #d4dbff);
            opacity: 0.95;
        }

        .right-panel {
            background: rgba(244, 248, 251, 0.72);
            position: relative;
            padding: 92px 88px 70px;
            overflow: hidden;
            backdrop-filter: blur(4px);
        }

        .right-panel::after {
            content: "";
            position: absolute;
            top: 34px;
            right: 32px;
            width: 82px;
            height: 82px;
            background-image: radial-gradient(rgba(36, 24, 117, 0.30) 1.3px, transparent 1.3px);
            background-size: 13px 13px;
            opacity: 0.55;
        }

        .brand {
            position: relative;
            z-index: 2;
            text-align: center;
            margin-bottom: 50px;
        }

        .brand h1 {
            margin: 0;
            color: #21156f;
            font-size: 31px;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .brand h1 span {
            color: #ef3f46;
        }

        .brand p {
            margin: 18px 0 0;
            color: #21156f;
            font-size: 26px;
            font-weight: 400;
        }

        .form-box {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 390px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            color: #1f2933;
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .form-group input {
            width: 100%;
            height: 54px;
            border: none;
            outline: none;
            border-radius: 999px;
            background: rgba(233, 241, 255, 0.92);
            color: #000000;
            padding: 0 24px;
            font-size: 16px;
            box-shadow:
                inset 0 2px 4px rgba(15, 23, 42, 0.08),
                0 3px 8px rgba(15, 23, 42, 0.08);
        }

        .form-group input:focus {
            box-shadow:
                0 0 0 2px rgba(36, 24, 117, 0.22),
                0 6px 16px rgba(15, 23, 42, 0.10);
        }

        .forgot-row {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 30px;
        }

        .forgot-row a {
            color: #4c3ea3;
            font-size: 15px;
            text-decoration: none;
        }

        .forgot-row a:hover {
            text-decoration: underline;
        }

        .btn-login,
        .btn-register {
            width: 100%;
            height: 58px;
            border: none;
            border-radius: 999px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn-login {
            background: rgba(255, 255, 255, 0.95);
            color: #21156f;
            box-shadow: 0 12px 25px rgba(40, 35, 98, 0.14);
            margin-bottom: 16px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 30px rgba(40, 35, 98, 0.20);
        }

        .btn-register {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #241875;
            color: #ffffff;
            box-shadow: 0 12px 25px rgba(36, 24, 117, 0.18);
        }

        .btn-register:hover {
            background: #1c125e;
            transform: translateY(-1px);
        }

        .alert {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 390px;
            margin: -24px auto 22px;
            border-radius: 14px;
            padding: 12px 16px;
            font-size: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-error ul {
            margin: 0;
            padding-left: 18px;
        }

        @media (max-width: 980px) {
            body {
                padding: 16px;
            }

            .login-wrapper {
                padding: 24px;
                min-height: auto;
            }

            .login-card {
                grid-template-columns: 1fr;
            }

            .left-panel {
                min-height: 430px;
                border-radius: 0;
            }

            .right-panel {
                padding: 56px 28px;
            }
        }

        @media (max-width: 520px) {
            .login-wrapper {
                padding: 12px;
                border-radius: 24px;
            }

            .left-panel {
                min-height: 360px;
                padding: 28px 22px 18px;
            }

            .rive-stage,
            #loginRiveCanvas {
                height: 310px;
            }

            .brand h1 {
                font-size: 26px;
            }

            .brand p {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <!-- ANIMASI BACKGROUND PALING BELAKANG -->
    <div class="page-bg-rive">
        <canvas id="pageBgRiveCanvas" width="1920" height="1080"></canvas>
    </div>

    <div class="page-bg-overlay"></div>

    <div class="login-wrapper">
        <div class="login-card">

            <div class="left-panel">
                <div class="rive-box">
                    <div class="rive-stage">
                        <canvas id="loginRiveCanvas" width="430" height="390"></canvas>

                        <div class="rive-loading" id="riveLoading">
                            Memuat animasi...
                        </div>

                        <div class="rive-error" id="riveError">
                            Animasi belum tampil.<br>
                            Pastikan file animasi orang ada di folder <b>public/rive</b>
                        </div>
                    </div>
                </div>

                <div class="bottom-line"></div>
            </div>

            <div class="right-panel">
                <div class="brand">
                    <h1>EDU-<span>TASK</span></h1>
                    <p>Welcome Back !</p>
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
                        <label for="email">EMAIL ID</label>
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
                        <label for="password">PASSWORD</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password"
                        >
                    </div>

                    <div class="forgot-row">
                        <a href="#">Forgot Password ?</a>
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
            </div>

        </div>
    </div>

    <script src="https://unpkg.com/@rive-app/canvas@2.31.2"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof rive === 'undefined') {
                console.error('Library Rive gagal dimuat. Pastikan internet aktif.');
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ANIMASI BACKGROUND PALING BELAKANG
            |--------------------------------------------------------------------------
            */
            const pageBgCanvas = document.getElementById('pageBgRiveCanvas');
            const pageBgRiveFile = "{{ asset('rive/7969-15341-color-morph.riv') }}";

            let pageBgAnimation = null;

            if (pageBgCanvas) {
                pageBgAnimation = new rive.Rive({
                    src: pageBgRiveFile,
                    canvas: pageBgCanvas,
                    autoplay: true,

                    layout: new rive.Layout({
                        fit: rive.Fit.Cover,
                        alignment: rive.Alignment.Center
                    }),

                    onLoad: function () {
                        pageBgAnimation.resizeDrawingSurfaceToCanvas();

                        const bgAnimations = pageBgAnimation.animationNames || [];
                        const bgStateMachines = pageBgAnimation.stateMachineNames || [];

                        console.log('Background Rive berhasil dimuat:', pageBgRiveFile);
                        console.log('Background animations:', bgAnimations);
                        console.log('Background state machines:', bgStateMachines);

                        if (bgStateMachines.length > 0) {
                            pageBgAnimation.play(bgStateMachines[0]);
                        } else if (bgAnimations.length > 0) {
                            pageBgAnimation.play(bgAnimations[0]);
                        }
                    },

                    onLoadError: function () {
                        console.error('Background Rive gagal dimuat. Cek file: public/rive/7969-15341-color-morph.riv');
                    }
                });
            }

            /*
            |--------------------------------------------------------------------------
            | ANIMASI ORANG DI PANEL KIRI
            |--------------------------------------------------------------------------
            */
            const canvas = document.getElementById('loginRiveCanvas');
            const loading = document.getElementById('riveLoading');
            const errorBox = document.getElementById('riveError');

            const riveFile = "{{ asset('rive/5063-10215-sk8r-boi.riv') }}";

            function hideMessage() {
                if (loading) loading.style.display = 'none';
                if (errorBox) errorBox.style.display = 'none';
            }

            function showError(message) {
                console.error(message);

                if (loading) loading.style.display = 'none';

                if (errorBox) {
                    errorBox.style.display = 'block';
                    errorBox.innerHTML = message;
                }
            }

            if (!canvas) {
                console.error('Canvas loginRiveCanvas tidak ditemukan.');
                return;
            }

            let riveAnimation = null;

            riveAnimation = new rive.Rive({
                src: riveFile,
                canvas: canvas,
                autoplay: true,

                layout: new rive.Layout({
                    fit: rive.Fit.Cover,
                    alignment: rive.Alignment.Center
                }),

                onLoad: function () {
                    hideMessage();

                    riveAnimation.resizeDrawingSurfaceToCanvas();

                    const animations = riveAnimation.animationNames || [];
                    const stateMachines = riveAnimation.stateMachineNames || [];

                    console.log('Login Rive berhasil dimuat:', riveFile);
                    console.log('Login animations:', animations);
                    console.log('Login state machines:', stateMachines);

                    if (stateMachines.length > 0) {
                        riveAnimation.play(stateMachines[0]);
                    } else if (animations.length > 0) {
                        riveAnimation.play(animations[0]);
                    } else {
                        showError('File Rive terbaca, tetapi tidak ada animation/state machine yang ditemukan.');
                    }
                },

                onLoadError: function () {
                    showError('File Rive gagal dimuat. Cek nama file di folder public/rive.');
                }
            });

            window.addEventListener('resize', function () {
                if (pageBgAnimation) {
                    pageBgAnimation.resizeDrawingSurfaceToCanvas();
                }

                if (riveAnimation) {
                    riveAnimation.resizeDrawingSurfaceToCanvas();
                }
            });
        });
    </script>
</body>
</html>