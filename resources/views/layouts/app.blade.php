<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LMS Praktikum') }}</title>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --bg: #f6f8fb;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --danger: #dc2626;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; font-family: Arial, Helvetica, sans-serif; color: var(--text); background: var(--bg); }
        a { color: inherit; text-decoration: none; }
        button, input, select, textarea { font: inherit; }

        .app-shell { min-height: 100vh; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            height: 64px;
            padding: 0 18px;
            background: var(--card);
            border-bottom: 1px solid var(--line);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .topbar-left, .topbar-right { display: flex; align-items: center; gap: 12px; }
        .hamburger-btn, .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            color: var(--text);
            cursor: pointer;
            transition: 0.15s ease;
        }
        .hamburger-btn:hover, .icon-btn:hover { background: #f1f5f9; }
        .hamburger-lines { width: 20px; display: grid; gap: 4px; }
        .hamburger-lines span { display: block; height: 2px; background: var(--text); border-radius: 999px; }

        .brand-title { font-weight: 800; letter-spacing: -0.02em; }
        .brand-subtitle { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            color: #fff;
            background: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
        }
        .user-name { font-size: 14px; font-weight: 700; line-height: 1.1; }
        .user-role { font-size: 12px; color: var(--muted); line-height: 1.1; margin-top: 2px; }

        .page-content { width: 100%; padding: 24px 18px 48px; }
        .content-container { width: 100%; max-width: 1280px; margin: 0 auto; }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            z-index: 80;
            background: rgba(15, 23, 42, 0.52);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }
        .sidebar-drawer {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 90;
            width: min(86vw, 320px);
            height: 100vh;
            overflow-y: auto;
            background: #fff;
            border-right: 1px solid var(--line);
            box-shadow: 20px 0 50px rgba(15, 23, 42, 0.18);
            transform: translateX(-105%);
            transition: transform 0.22s ease;
        }
        body.sidebar-open { overflow: hidden; }
        body.sidebar-open .sidebar-overlay { opacity: 1; pointer-events: auto; }
        body.sidebar-open .sidebar-drawer { transform: translateX(0); }

        .sidebar-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 20px;
            border-bottom: 1px solid var(--line);
        }
        .sidebar-title { font-weight: 800; font-size: 18px; }
        .sidebar-subtitle { margin-top: 4px; color: var(--muted); font-size: 13px; }
        .sidebar-user { padding: 16px 20px; border-bottom: 1px solid var(--line); background: #f8fafc; }
        .sidebar-user-name { font-weight: 800; }
        .sidebar-user-email { margin-top: 4px; color: var(--muted); font-size: 13px; word-break: break-all; }
        .sidebar-user-role { margin-top: 8px; display: inline-flex; padding: 4px 9px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; font-size: 12px; font-weight: 700; }
        .sidebar-section { padding: 16px 14px 4px; color: var(--muted); font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; }
        .sidebar-nav { padding: 6px 10px 20px; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 12px;
            border-radius: 12px;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
            transition: 0.15s ease;
        }
        .sidebar-link:hover { background: #f1f5f9; color: var(--primary); }
        .sidebar-link.active { background: #dbeafe; color: #1d4ed8; }
        .sidebar-icon { width: 22px; text-align: center; flex: 0 0 22px; }
        .sidebar-logout { width: 100%; border: 0; text-align: left; background: transparent; cursor: pointer; }

        .card, .stat-card, .action-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 20px;
            padding: 22px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 20px;
        }
        .page-title { margin: 0; font-size: 26px; line-height: 1.2; font-weight: 800; letter-spacing: -0.03em; }
        .page-description { margin: 8px 0 0; color: var(--muted); line-height: 1.5; }
        .grid { display: grid; gap: 16px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 12px;
            padding: 10px 14px;
            border: 1px solid var(--line);
            background: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-primary { border-color: var(--primary); background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }

        .alert { padding: 12px 14px; border-radius: 14px; margin-bottom: 16px; border: 1px solid var(--line); background: #fff; }
        .alert-success { background: #ecfdf5; border-color: #bbf7d0; color: #166534; }
        .alert-error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid var(--line); text-align: left; }
        th { color: #475569; font-size: 12px; text-transform: uppercase; letter-spacing: 0.06em; background: #f8fafc; }



        .form-card { background:#fff; border:1px solid var(--line); border-radius:18px; padding:22px; box-shadow:0 8px 24px rgba(15,23,42,.04); }
        .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
        .form-group { display:block; margin-bottom:16px; }
        .form-label { display:block; margin-bottom:7px; font-size:14px; font-weight:700; color:#334155; }
        .required { color:#dc2626; }
        .form-control { width:100%; border:1px solid #cbd5e1; border-radius:12px; padding:10px 12px; background:#fff; color:#0f172a; outline:none; }
        .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(37,99,235,.13); }
        textarea.form-control { min-height:110px; resize:vertical; }
        .form-help { margin-top:6px; color:var(--muted); font-size:12px; line-height:1.45; }
        .form-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; padding-top:18px; border-top:1px solid var(--line); }
        .checkbox-row { display:flex; align-items:center; gap:10px; }
        .checkbox-row input { width:18px; height:18px; }
        .table-card { background:#fff; border:1px solid var(--line); border-radius:18px; overflow:hidden; box-shadow:0 8px 24px rgba(15,23,42,.04); }
        .toolbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
        .actions-inline { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        .btn-sm { padding:7px 10px; border-radius:10px; font-size:13px; }
        .btn-danger { border-color:#fecaca; background:#fef2f2; color:#991b1b; }
        .badge { display:inline-flex; align-items:center; border-radius:999px; padding:4px 9px; font-size:12px; font-weight:700; background:#f1f5f9; color:#334155; }
        .badge-green { background:#dcfce7; color:#166534; }
        .badge-red { background:#fee2e2; color:#991b1b; }
        .badge-blue { background:#dbeafe; color:#1d4ed8; }

        @media (max-width: 900px) {
            .grid-2, .grid-3, .grid-4, .form-grid { grid-template-columns: 1fr; }
            .page-header { display: block; }
            .brand-subtitle, .user-role { display: none; }
            .user-chip { padding: 5px; }
            .user-name { display: none; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        @include('partials.sidebar')

        <div class="main-area">
            @include('partials.navbar')

            <main class="page-content">
                <div class="content-container">
                    @includeIf('partials.flash')
                    @includeIf('partials.validation-errors')

                    @yield('content')

                    @isset($slot)
                        {{ $slot }}
                    @endisset
                </div>
            </main>
        </div>
    </div>

    @livewireScripts

    @if (view()->exists('components.notification-echo-listener'))
        @include('components.notification-echo-listener')
    @endif

    <script>
        (function () {
            const body = document.body;

            function openSidebar() {
                body.classList.add('sidebar-open');
            }

            function closeSidebar() {
                body.classList.remove('sidebar-open');
            }

            document.addEventListener('click', function (event) {
                const openButton = event.target.closest('[data-sidebar-open]');
                const closeButton = event.target.closest('[data-sidebar-close]');
                const navLink = event.target.closest('[data-sidebar-link]');

                if (openButton) {
                    event.preventDefault();
                    openSidebar();
                }

                if (closeButton || navLink) {
                    closeSidebar();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            });
        })();
    </script>
</body>
</html>
