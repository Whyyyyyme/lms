<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'LMS Praktikum'))</title>

    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @livewireStyles

    <style>
        :root {
            --primary: #1d4ed8;
            --primary-soft: #dbeafe;
            --primary-dark: #0f2f6f;

            --sidebar: #0f172a;
            --sidebar-soft: #172554;
            --sidebar-line: rgba(255, 255, 255, 0.10);

            --bg: #f4f7fb;
            --bg-soft: #eef4ff;
            --card: #ffffff;

            --text: #0f172a;
            --muted: #64748b;
            --muted-dark: #475569;

            --line: #e2e8f0;
            --line-soft: #edf2f7;

            --success: #16a34a;
            --success-soft: #dcfce7;

            --warning: #d97706;
            --warning-soft: #fef3c7;

            --danger: #dc2626;
            --danger-soft: #fee2e2;

            --info: #0284c7;
            --info-soft: #e0f2fe;

            --radius-sm: 10px;
            --radius-md: 14px;
            --radius-lg: 18px;
            --radius-xl: 24px;

            --shadow-sm: 0 4px 12px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 12px 32px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 24px 60px rgba(15, 23, 42, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(29, 78, 216, 0.10), transparent 32rem),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 42%, #f8fafc 100%);
        }

        body {
            overflow-x: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        img,
        svg {
            max-width: 100%;
        }

        [x-cloak] {
            display: none !important;
        }

        .app-shell {
            min-height: 100vh;
        }

        .main-area {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /*
        |--------------------------------------------------------------------------
        | Topbar / Navbar
        |--------------------------------------------------------------------------
        */

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 68px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.86);
            border-bottom: 1px solid rgba(226, 232, 240, 0.90);
            backdrop-filter: blur(18px);
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.03);
        }

        .topbar-left,
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .brand-title {
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #0f172a;
            line-height: 1.1;
        }

        .brand-subtitle {
            margin-top: 3px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.2;
        }

        .hamburger-btn,
        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 43px;
            height: 43px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #ffffff;
            color: var(--text);
            cursor: pointer;
            transition: 0.18s ease;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
        }

        .hamburger-btn:hover,
        .icon-btn:hover {
            transform: translateY(-1px);
            background: #f8fafc;
            border-color: #cbd5e1;
            box-shadow: var(--shadow-sm);
        }

        .hamburger-lines {
            width: 20px;
            display: grid;
            gap: 4px;
        }

        .hamburger-lines span {
            display: block;
            height: 2px;
            background: var(--text);
            border-radius: 999px;
        }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 260px;
            padding: 6px 10px 6px 6px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #ffffff;
            box-shadow: 0 3px 10px rgba(15, 23, 42, 0.04);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 34px;
            border-radius: 999px;
            color: #ffffff;
            background: linear-gradient(135deg, #1d4ed8, #0f172a);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: -0.02em;
        }

        .user-name {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.1;
        }

        .user-role {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-top: 2px;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.1;
        }

        /*
        |--------------------------------------------------------------------------
        | Sidebar Drawer
        |--------------------------------------------------------------------------
        */

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            z-index: 80;
            background: rgba(15, 23, 42, 0.56);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.22s ease;
            backdrop-filter: blur(3px);
        }

        .sidebar-drawer {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 90;
            width: min(88vw, 336px);
            height: 100vh;
            overflow-y: auto;
            color: #e5e7eb;
            background:
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.26), transparent 20rem),
                linear-gradient(180deg, #0f172a 0%, #111827 54%, #020617 100%);
            border-right: 1px solid var(--sidebar-line);
            box-shadow: 24px 0 70px rgba(15, 23, 42, 0.30);
            transform: translateX(-105%);
            transition: transform 0.24s ease;
        }

        body.sidebar-open {
            overflow: hidden;
        }

        body.sidebar-open .sidebar-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        body.sidebar-open .sidebar-drawer {
            transform: translateX(0);
        }

        .sidebar-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 22px 20px;
            border-bottom: 1px solid var(--sidebar-line);
        }

        .sidebar-title {
            font-weight: 900;
            font-size: 18px;
            letter-spacing: -0.03em;
            color: #ffffff;
        }

        .sidebar-subtitle {
            margin-top: 5px;
            color: #94a3b8;
            font-size: 13px;
            line-height: 1.45;
        }

        .sidebar-user {
            padding: 16px 20px;
            border-bottom: 1px solid var(--sidebar-line);
            background: rgba(255, 255, 255, 0.045);
        }

        .sidebar-user-name {
            font-weight: 900;
            color: #ffffff;
        }

        .sidebar-user-email {
            margin-top: 5px;
            color: #94a3b8;
            font-size: 13px;
            word-break: break-all;
        }

        .sidebar-user-role {
            margin-top: 10px;
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.18);
            border: 1px solid rgba(147, 197, 253, 0.22);
            color: #bfdbfe;
            font-size: 12px;
            font-weight: 800;
        }

        .sidebar-section {
            padding: 18px 16px 6px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.11em;
        }

        .sidebar-nav {
            padding: 8px 10px 22px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 44px;
            padding: 11px 12px;
            border-radius: 14px;
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 750;
            margin-bottom: 5px;
            transition: 0.16s ease;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.96), rgba(30, 64, 175, 0.82));
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.22);
        }

        .sidebar-icon {
            width: 22px;
            text-align: center;
            flex: 0 0 22px;
        }

        .sidebar-logout {
            width: 100%;
            border: 0;
            text-align: left;
            background: transparent;
            cursor: pointer;
        }

        /*
        |--------------------------------------------------------------------------
        | Page Layout
        |--------------------------------------------------------------------------
        */

        .page-content {
            width: 100%;
            flex: 1;
            padding: 28px 20px 52px;
        }

        .content-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
        }

        .page-header {
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            margin-bottom: 20px;
            padding: 24px;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(239, 246, 255, 0.92));
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
        }

        .page-header::after {
            content: "";
            position: absolute;
            right: -70px;
            top: -80px;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: rgba(29, 78, 216, 0.08);
            pointer-events: none;
        }

        .page-title {
            position: relative;
            z-index: 1;
            margin: 0;
            font-size: clamp(24px, 3vw, 32px);
            line-height: 1.15;
            font-weight: 950;
            letter-spacing: -0.045em;
            color: #0f172a;
        }

        .page-description {
            position: relative;
            z-index: 1;
            max-width: 760px;
            margin: 9px 0 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 14px;
        }

        .section-title {
            margin: 0;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .section-subtitle {
            margin-top: 4px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        /*
        |--------------------------------------------------------------------------
        | Cards, Grid, Dashboard Components
        |--------------------------------------------------------------------------
        */

        .grid {
            display: grid;
            gap: 16px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .grid-5 {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .card,
        .stat-card,
        .action-card,
        .table-card,
        .form-card {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(226, 232, 240, 0.92);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .card {
            padding: 20px;
        }

        .stat-card {
            padding: 18px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            right: -34px;
            top: -34px;
            width: 96px;
            height: 96px;
            border-radius: 999px;
            background: rgba(29, 78, 216, 0.07);
        }

        .stat-label {
            position: relative;
            z-index: 1;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .stat-value {
            position: relative;
            z-index: 1;
            margin-top: 8px;
            font-size: 28px;
            font-weight: 950;
            letter-spacing: -0.04em;
            color: #0f172a;
        }

        .stat-note {
            position: relative;
            z-index: 1;
            margin-top: 6px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .dashboard-hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 18px;
            padding: 26px;
            border-radius: 28px;
            color: #ffffff;
            background:
                radial-gradient(circle at top right, rgba(96, 165, 250, 0.34), transparent 22rem),
                linear-gradient(135deg, #0f172a 0%, #1e3a8a 52%, #1d4ed8 100%);
            box-shadow: var(--shadow-md);
        }

        .dashboard-hero::after {
            content: "";
            position: absolute;
            right: -86px;
            bottom: -100px;
            width: 260px;
            height: 260px;
            border-radius: 999px;
            border: 38px solid rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .dashboard-hero > * {
            position: relative;
            z-index: 1;
        }

        .dashboard-hero .eyebrow,
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.10em;
            color: #bfdbfe;
        }

        .dashboard-hero h1 {
            margin: 0;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.08;
            font-weight: 950;
            letter-spacing: -0.06em;
        }

        .dashboard-hero p {
            max-width: 780px;
            margin: 12px 0 0;
            color: #dbeafe;
            line-height: 1.65;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .course-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .course-card {
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 210px;
            padding: 20px;
            border-radius: 22px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: var(--shadow-sm);
            transition: 0.18s ease;
        }

        .course-card:hover {
            transform: translateY(-3px);
            border-color: rgba(37, 99, 235, 0.30);
            box-shadow: var(--shadow-md);
        }

        .course-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 5px;
            background: linear-gradient(90deg, #1d4ed8, #38bdf8);
        }

        .course-code {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            max-width: 100%;
            padding: 5px 9px;
            border-radius: 999px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 12px;
            font-weight: 900;
        }

        .course-title {
            margin: 14px 0 0;
            font-size: 18px;
            line-height: 1.28;
            font-weight: 950;
            letter-spacing: -0.035em;
            color: #0f172a;
        }

        .course-meta {
            margin-top: 8px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }

        .course-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: auto;
            padding-top: 16px;
        }

        .metric-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .metric-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 9px;
            border-radius: 999px;
            background: #f8fafc;
            color: #334155;
            border: 1px solid #e2e8f0;
            font-size: 12px;
            font-weight: 800;
        }

        .list-stack {
            display: grid;
            gap: 10px;
        }

        .list-item,
        .module-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            padding: 14px;
            border: 1px solid var(--line-soft);
            border-radius: 16px;
            background: #ffffff;
            transition: 0.15s ease;
        }

        .list-item:hover,
        .module-row:hover {
            border-color: #cbd5e1;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        .item-title {
            margin: 0;
            font-size: 15px;
            font-weight: 900;
            color: #0f172a;
        }

        .item-meta {
            margin-top: 5px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        /*
        |--------------------------------------------------------------------------
        | Button
        |--------------------------------------------------------------------------
        */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 40px;
            border-radius: 13px;
            padding: 10px 14px;
            border: 1px solid var(--line);
            background: #ffffff;
            color: #0f172a;
            font-weight: 850;
            cursor: pointer;
            transition: 0.16s ease;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .btn-primary {
            border-color: var(--primary);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.18);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            border-color: #1d4ed8;
        }

        .btn-danger {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .btn-danger:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .btn-sm {
            min-height: 34px;
            padding: 7px 10px;
            border-radius: 10px;
            font-size: 13px;
        }

        /*
        |--------------------------------------------------------------------------
        | Badge / Status
        |--------------------------------------------------------------------------
        */

        .badge,
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
            max-width: 100%;
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 850;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid transparent;
        }

        .badge-green,
        .status-success {
            background: var(--success-soft);
            color: #166534;
        }

        .badge-red,
        .status-danger {
            background: var(--danger-soft);
            color: #991b1b;
        }

        .badge-blue,
        .status-info {
            background: var(--primary-soft);
            color: #1d4ed8;
        }

        .badge-yellow,
        .status-warning {
            background: var(--warning-soft);
            color: #92400e;
        }

        .status-muted {
            background: #f1f5f9;
            color: #475569;
        }

        /*
        |--------------------------------------------------------------------------
        | Table
        |--------------------------------------------------------------------------
        */

        .table-card {
            overflow: hidden;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: middle;
        }

        th {
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            background: #f8fafc;
            font-weight: 900;
        }

        td {
            color: #1e293b;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Form
        |--------------------------------------------------------------------------
        */

        .form-card {
            padding: 22px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-group {
            display: block;
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: 850;
            color: #334155;
        }

        .required {
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            min-height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 13px;
            padding: 10px 12px;
            background: #ffffff;
            color: #0f172a;
            outline: none;
            transition: 0.15s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        textarea.form-control {
            min-height: 112px;
            resize: vertical;
        }

        .form-help {
            margin-top: 6px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
            padding-top: 18px;
            border-top: 1px solid var(--line);
            flex-wrap: wrap;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-row input {
            width: 18px;
            height: 18px;
        }

        /*
        |--------------------------------------------------------------------------
        | Toolbar / Alert
        |--------------------------------------------------------------------------
        */

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .actions-inline {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .alert {
            padding: 13px 15px;
            border-radius: 15px;
            margin-bottom: 16px;
            border: 1px solid var(--line);
            background: #ffffff;
            box-shadow: 0 3px 12px rgba(15, 23, 42, 0.03);
        }

        .alert-success {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        /*
        |--------------------------------------------------------------------------
        | Empty State
        |--------------------------------------------------------------------------
        */

        .empty-state {
            padding: 28px;
            text-align: center;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.70);
            color: var(--muted);
        }

        .empty-state-title {
            margin: 0;
            color: #0f172a;
            font-size: 17px;
            font-weight: 900;
        }

        .empty-state-text {
            margin: 8px auto 0;
            max-width: 540px;
            line-height: 1.55;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1100px) {
            .course-grid,
            .grid-4,
            .grid-5 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .grid-2,
            .grid-3,
            .grid-4,
            .grid-5,
            .course-grid,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                display: block;
                padding: 20px;
            }

            .section-header {
                display: block;
            }

            .dashboard-hero {
                padding: 22px;
                border-radius: 22px;
            }

            .brand-subtitle,
            .user-role {
                display: none;
            }

            .user-chip {
                padding: 5px;
            }

            .user-name {
                display: none;
            }

            .page-content {
                padding: 20px 14px 44px;
            }

            .hero-actions,
            .form-actions {
                align-items: stretch;
            }

            .hero-actions .btn,
            .form-actions .btn {
                width: 100%;
            }

            .list-item,
            .module-row {
                display: block;
            }
        }

        @media (max-width: 520px) {
            .topbar {
                padding-inline: 12px;
            }

            .brand-title {
                font-size: 15px;
            }

            .page-title {
                font-size: 24px;
            }

            .card,
            .form-card,
            .stat-card {
                border-radius: 16px;
            }

            th,
            td {
                padding: 11px 12px;
            }
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