<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LMS Praktikum</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #edf1f5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1180px;
            min-height: 710px;
            background: #eef2f5;
            border-radius: 34px;
            box-shadow: 0 14px 80px rgba(0,0,0,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px;
        }

        .login-card {
            width: 100%;
            max-width: 1040px;
            min-height: 560px;
            background: #f9fbfd;
            box-shadow: 0 10px 35px rgba(0,0,0,0.18);
            display: grid;
            grid-template-columns: 430px 1fr;
            overflow: hidden;
        }

        .left-panel {
            background: #1f1973;
            color: white;
            padding: 28px 24px 16px;
            position: relative;
            border-top-right-radius: 36px;
            border-bottom-right-radius: 36px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .dots {
            width: 84px;
            height: 84px;
            background-image: radial-gradient(rgba(255,255,255,0.45) 1.5px, transparent 1.5px);
            background-size: 12px 12px;
            opacity: 0.7;
        }

        .left-image-box {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 0;
        }

        .left-image-box img {
            max-width: 350%;
            max-height: 450px;
            object-fit: contain;
            display: block;
        }

        .left-bottom-line {
            width: 100%;
            height: 10px;
            border-radius: 8px;
            background: linear-gradient(to right, #8691dc, #c6d0ff);
            opacity: 0.9;
        }

        .right-panel {
            background: #f3f6f8;
            position: relative;
            padding: 70px 90px;
        }

        .right-panel::after {
            content: "";
            position: absolute;
            top: 20px;
            right: 18px;
            width: 72px;
            height: 72px;
            background-image: radial-gradient(rgba(31,25,115,0.35) 1.3px, transparent 1.3px);
            background-size: 12px 12px;
            opacity: 0.45;
        }

        .brand {
            text-align: center;
            margin-bottom: 34px;
        }

        .brand h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            color: #211a72;
            letter-spacing: 0.3px;
        }

        .brand h1 span {
            color: #f04040;
        }

        .brand p {
            margin: 8px 0 0;
            font-size: 22px;
            color: #2f2f74;
            font-weight: 500;
        }

        .form-box {
            width: 100%;
            max-width: 360px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #303030;
            letter-spacing: 0.2px;
        }

        .form-group input {
            width: 100%;
            height: 42px;
            border: none;
            border-radius: 30px;
            background: #ffffff;
            padding: 0 18px;
            font-size: 15px;
            color: #333;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.08), 0 2px 4px rgba(0,0,0,0.05);
            outline: none;
        }

        .form-group input:focus {
            box-shadow: 0 0 0 2px rgba(33,26,114,0.16);
        }

        .forgot-row {
            text-align: right;
            margin-top: -4px;
            margin-bottom: 22px;
        }

        .forgot-row a {
            text-decoration: none;
            font-size: 14px;
            color: #59549b;
        }

        .forgot-row a:hover {
            text-decoration: underline;
        }

        .btn-login,
        .btn-register {
            width: 100%;
            height: 46px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .btn-login {
            background: #ffffff;
            color: #1f1973;
            box-shadow: 0 8px 18px rgba(89,84,155,0.18);
            margin-bottom: 14px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
        }

        .btn-register {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            background: #1f1973;
            color: white;
            box-shadow: 0 8px 18px rgba(31,25,115,0.18);
        }

        .btn-register:hover {
            background: #17125a;
        }

        .alert {
            width: 100%;
            max-width: 360px;
            margin: 0 auto 18px;
            border-radius: 12px;
            padding: 12px 14px;
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

        @media (max-width: 992px) {
            .login-card {
                grid-template-columns: 1fr;
            }

            .left-panel {
                border-radius: 0;
                min-height: 340px;
            }

            .right-panel {
                padding: 50px 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">

            {{-- Panel kiri --}}
            <div class="left-panel">
                <div class="dots"></div>

                <div class="left-image-box">
                    {{-- Ganti file gambar ini sesuai kebutuhan --}}
                    <img src="{{ asset('images/pak.png') }}" alt="Ilustrasi Login">
                </div>

                <div class="left-bottom-line"></div>
            </div>

            {{-- Panel kanan --}}
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

                    <a href="{{ route('register') }}" class="btn-register">
                        Register
                    </a>
                </form>
            </div>

        </div>
    </div>
</body>
</html>