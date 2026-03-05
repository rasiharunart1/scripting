@php $user = Auth::user(); @endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'MDPOWER - Dashboard')</title>
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    @stack('styles')

    <style>
        /* ============================================================
           GLASSMORPHISM DARK BLUE THEME – MAIN APP OVERRIDE
           ============================================================ */

        :root {
            --gb900: #020c24;
            --gb800: #071535;
            --gb700: #0a1f55;
            --gb600: #0d2a7a;
            --gb500: #1a3fa8;
            --gb-accent: #2563eb;
            --gb-glow: rgba(37, 99, 235, 0.5);
            --glass-sidebar: rgba(7, 15, 45, 0.82);
            --glass-topbar: rgba(6, 12, 38, 0.78);
            --glass-card: rgba(255, 255, 255, 0.055);
            --glass-border: rgba(96, 165, 250, 0.18);
            --text-bright: #e8f0fe;
            --text-muted-gb: rgba(186, 224, 255, 0.6);
        }

        /* ── Body & Background ── */
        body#page-top {
            background: radial-gradient(ellipse at 70% 20%, #0d2472 0%, #061030 45%, #020817 100%) !important;
            background-attachment: fixed !important;
            background-color: #020c24 !important;
            color: var(--text-bright) !important;
        }

        /* bg overlay div (NOT ::before — avoids topbar offset bug with SB Admin 2) */
        .gb-bg-overlay {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 15% 85%, rgba(13,42,114,.4) 0%, transparent 50%),
                radial-gradient(circle at 85% 10%, rgba(26,63,168,.28) 0%, transparent 45%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── Wrapper ── */
        #wrapper {
            position: relative;
            z-index: 1;
        }

        /* ── Footer ── */
        .sticky-footer,
        footer.sticky-footer {
            background: rgba(6, 12, 38, 0.88) !important;
            backdrop-filter: blur(16px) !important;
            -webkit-backdrop-filter: blur(16px) !important;
            border-top: 1px solid rgba(96, 165, 250, 0.18) !important;
            color: rgba(186, 224, 255, 0.6) !important;
        }

        .sticky-footer .copyright span,
        footer .copyright span {
            color: rgba(186, 224, 255, 0.5) !important;
            font-size: .8rem;
        }

        /* ── Sidebar ── */
        .sidebar {
            background: var(--glass-sidebar) !important;
            backdrop-filter: blur(20px) saturate(150%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(150%) !important;
            border-right: 1px solid var(--glass-border) !important;
            box-shadow: 4px 0 32px rgba(0, 0, 0, 0.5), 2px 0 0 rgba(96, 165, 250, 0.08) inset !important;
        }

        .sidebar .nav-link {
            color: rgba(186, 224, 255, 0.75) !important;
            border-radius: 0;
            margin: 0 !important;
            padding: 12px 20px !important;
            transition: background .18s, color .18s;
            position: relative;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-item.active .nav-link {
            color: #fff !important;
            background: rgba(37, 99, 235, 0.22) !important;
            box-shadow: none !important;
            border-left: 3px solid #60a5fa !important;
            padding-left: 17px !important;
        }

        .sidebar .nav-link i {
            color: rgba(147, 197, 253, 0.7) !important;
        }

        .sidebar .nav-item.active .nav-link i,
        .sidebar .nav-link:hover i {
            color: #93c5fd !important;
        }

        .sidebar-brand {
            color: #fff !important;
            border-bottom: 1px solid var(--glass-border) !important;
        }

        .sidebar-brand-text { color: #e0eaff !important; }

        .sidebar-divider {
            border-top: 1px solid rgba(96, 165, 250, 0.12) !important;
        }

        .sidebar-heading {
            color: rgba(96, 165, 250, 0.5) !important;
            font-size: 0.65rem;
            letter-spacing: 1px;
        }

        /* Sidebar toggle button */
        #sidebarToggle {
            background: rgba(37, 99, 235, 0.2) !important;
            color: #93c5fd !important;
            border: 1px solid rgba(96, 165, 250, 0.2) !important;
        }

        #sidebarToggle:hover {
            background: rgba(37, 99, 235, 0.35) !important;
        }

        /* ── Topbar ── */
        .topbar {
            background: var(--glass-topbar) !important;
            backdrop-filter: blur(20px) saturate(150%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(150%) !important;
            border-bottom: 1px solid var(--glass-border) !important;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4) !important;
        }

        .topbar .nav-link {
            color: rgba(186, 224, 255, 0.75) !important;
        }

        .topbar .nav-link:hover { color: #fff !important; }

        .topbar .btn-link { color: rgba(186, 224, 255, 0.75) !important; }

        /* ── Content Wrapper ── */
        #content-wrapper {
            background: transparent !important;
        }

        /* ── Cards ── */
        .card {
            background: rgba(13, 27, 72, 0.72) !important;
            /* NO backdrop-filter here — it creates a stacking context that traps Bootstrap modals */
            border: 1px solid var(--glass-border) !important;
            border-radius: 16px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,0.45), 0 0 0 1px rgba(255,255,255,0.04) inset !important;
            color: var(--text-bright) !important;
        }

        /* ══════════════════════════════════════════
           GLASSMORPHISM MODAL – full dark blue theme
           ══════════════════════════════════════════ */

        /* Backdrop: dark blue tint instead of plain black */
        .modal-backdrop {
            z-index: 99040 !important;
            background-color: rgba(2, 8, 32, 0.75) !important;
        }

        .modal {
            z-index: 99050 !important;
        }

        .modal-dialog {
            pointer-events: all !important;
        }

        /* Glass card */
        .modal-content {
            pointer-events: all !important;
            background: rgba(7, 14, 50, 0.88) !important;
            backdrop-filter: blur(28px) saturate(160%) !important;
            -webkit-backdrop-filter: blur(28px) saturate(160%) !important;
            border: 1px solid rgba(96, 165, 250, 0.22) !important;
            border-radius: 18px !important;
            box-shadow:
                0 24px 64px rgba(0, 0, 0, 0.6),
                0 0 0 1px rgba(255,255,255,0.05) inset,
                0 1px 0 rgba(96,165,250,0.15) inset !important;
            overflow: hidden;
        }

        /* Header – gradient dark blue with top glow line */
        .modal-header,
        .modal-header.bg-primary {
            background: linear-gradient(135deg, rgba(13,36,110,0.95) 0%, rgba(9,22,75,0.98) 100%) !important;
            border-bottom: 1px solid rgba(96, 165, 250, 0.2) !important;
            border-radius: 18px 18px 0 0 !important;
            padding: 16px 20px !important;
            position: relative;
        }

        .modal-header::after {
            content: '';
            position: absolute;
            top: 0; left: 10%; right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(96,165,250,0.5), transparent);
        }

        .modal-title {
            color: #e8f0fe !important;
            font-weight: 700 !important;
            font-size: .95rem !important;
            letter-spacing: .3px;
        }

        .modal-header .close,
        .modal-header .close.text-white {
            color: rgba(186, 224, 255, 0.7) !important;
            text-shadow: none !important;
            opacity: 1 !important;
            transition: color .15s, transform .15s !important;
        }

        .modal-header .close:hover {
            color: #fff !important;
            transform: rotate(90deg) !important;
        }

        /* Body */
        .modal-body {
            background: transparent !important;
            color: var(--text-bright) !important;
            padding: 20px 22px !important;
        }

        /* Footer */
        .modal-footer {
            background: rgba(4, 10, 38, 0.5) !important;
            border-top: 1px solid rgba(96, 165, 250, 0.15) !important;
            border-radius: 0 0 18px 18px !important;
            padding: 14px 20px !important;
        }

        /* Form controls inside modal */
        .modal .form-control {
            background: rgba(255, 255, 255, 0.07) !important;
            border: 1px solid rgba(96, 165, 250, 0.25) !important;
            color: #e8f0fe !important;
            border-radius: 10px !important;
        }

        .modal .form-control::placeholder {
            color: rgba(148, 163, 184, 0.45) !important;
        }

        .modal .form-control:focus {
            background: rgba(255, 255, 255, 0.11) !important;
            border-color: rgba(96, 165, 250, 0.55) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
            color: #fff !important;
        }

        .modal label {
            color: rgba(147, 197, 253, 0.85) !important;
            font-size: .78rem !important;
            font-weight: 600 !important;
            letter-spacing: .4px !important;
        }

        .modal .form-text {
            color: rgba(148, 163, 184, 0.55) !important;
            font-size: .7rem !important;
        }

        /* Alerts inside modal */
        .modal .alert-info {
            background: rgba(59, 130, 246, 0.12) !important;
            border: 1px solid rgba(96, 165, 250, 0.3) !important;
            color: #93c5fd !important;
            border-radius: 10px !important;
        }

        .modal .alert-warning {
            background: rgba(245, 158, 11, 0.12) !important;
            border: 1px solid rgba(251, 191, 36, 0.3) !important;
            color: #fcd34d !important;
            border-radius: 10px !important;
        }

        .modal .alert-danger {
            background: rgba(239, 68, 68, 0.12) !important;
            border: 1px solid rgba(248, 113, 113, 0.3) !important;
            color: #fca5a5 !important;
            border-radius: 10px !important;
        }

        .modal .alert-success {
            background: rgba(16, 185, 129, 0.12) !important;
            border: 1px solid rgba(52, 211, 153, 0.3) !important;
            color: #6ee7b7 !important;
            border-radius: 10px !important;
        }

        /* Buttons inside modal */
        .modal .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            border-color: transparent !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.45) !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
        }

        .modal .btn-primary:hover {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6) !important;
            transform: translateY(-1px);
        }

        .modal .btn-secondary {
            background: rgba(255,255,255,0.08) !important;
            border: 1px solid rgba(96,165,250,0.25) !important;
            color: rgba(186, 224, 255, 0.85) !important;
            border-radius: 10px !important;
            font-weight: 500 !important;
        }

        .modal .btn-secondary:hover {
            background: rgba(255,255,255,0.14) !important;
            color: #fff !important;
        }

        .modal .btn-light {
            background: rgba(255,255,255,0.08) !important;
            border: 1px solid rgba(96,165,250,0.2) !important;
            color: rgba(186, 224, 255, 0.75) !important;
            border-radius: 10px !important;
        }

        .modal .btn-light:hover {
            background: rgba(255,255,255,0.14) !important;
            color: #fff !important;
        }

        .card-header {
            background: rgba(255, 255, 255, 0.04) !important;
            border-bottom: 1px solid var(--glass-border) !important;
            color: var(--text-bright) !important;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-body { color: var(--text-bright) !important; }

        /* ── Border-left accent cards ── */
        .border-left-primary { border-left: 4px solid #2563eb !important; }
        .border-left-success { border-left: 4px solid #10b981 !important; }
        .border-left-warning { border-left: 4px solid #f59e0b !important; }
        .border-left-danger  { border-left: 4px solid #ef4444 !important; }
        .border-left-info    { border-left: 4px solid #06b6d4 !important; }

        /* ── Text colors ── */
        .text-primary { color: #60a5fa !important; }
        .text-gray-800, h1, h2, h3, h4, h5, h6 { color: var(--text-bright) !important; }
        .text-gray-900 { color: #e8f0fe !important; }
        .text-gray-600, .text-gray-500 { color: var(--text-muted-gb) !important; }
        p, span, label, td, th, li { color: var(--text-bright); }

        /* ── Tables ── */
        .table {
            color: var(--text-bright) !important;
        }

        .table thead th {
            background: rgba(13, 42, 114, 0.55) !important;
            color: #93c5fd !important;
            border-bottom: 1px solid var(--glass-border) !important;
            border-top: none !important;
            font-size: .72rem;
            letter-spacing: .6px;
            text-transform: uppercase;
        }

        .table td, .table th {
            border-color: rgba(96, 165, 250, 0.1) !important;
        }

        .table-bordered {
            border: 1px solid rgba(96, 165, 250, 0.12) !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background: rgba(37, 99, 235, 0.06) !important;
        }

        .table-hover tbody tr:hover {
            background: rgba(37, 99, 235, 0.12) !important;
            color: #fff !important;
        }

        /* ── Inputs & Forms ── */
        .form-control {
            background: rgba(255, 255, 255, 0.07) !important;
            border: 1px solid rgba(96, 165, 250, 0.25) !important;
            color: var(--text-bright) !important;
            border-radius: 10px !important;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control::placeholder { color: rgba(148, 163, 184, 0.45) !important; }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.11) !important;
            border-color: rgba(96, 165, 250, 0.55) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
            color: #fff !important;
        }

        /* ── Buttons ── */
        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            border-color: transparent !important;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.45) !important;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6) !important;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.1) !important;
            border-color: rgba(96,165,250,0.25) !important;
            color: var(--text-bright) !important;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.16) !important;
            color: #fff !important;
        }

        /* ── Badges ── */
        .badge-primary { background: #2563eb !important; }
        .badge-success { background: #059669 !important; }
        .badge-warning { background: #d97706 !important; }
        .badge-danger  { background: #dc2626 !important; }

        /* ── Dropdowns ── */
        .dropdown-menu {
            background: rgba(7, 15, 53, 0.92) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 14px !important;
            box-shadow: 0 12px 36px rgba(0,0,0,0.5) !important;
        }

        .dropdown-item {
            color: rgba(186, 224, 255, 0.8) !important;
            border-radius: 8px;
            margin: 2px 6px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .dropdown-item:hover {
            background: rgba(37, 99, 235, 0.25) !important;
            color: #fff !important;
        }

        .dropdown-divider {
            border-color: rgba(96, 165, 250, 0.15) !important;
        }

        /* ── Modal ── */
        .modal-content {
            background: rgba(7, 15, 53, 0.95) !important;
            backdrop-filter: blur(24px) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 18px !important;
            box-shadow: 0 20px 60px rgba(0,0,0,0.7) !important;
            color: var(--text-bright) !important;
        }

        .modal-header {
            border-bottom: 1px solid rgba(96,165,250,0.2) !important;
        }

        .modal-footer {
            border-top: 1px solid rgba(96,165,250,0.2) !important;
        }

        .modal-title { color: #e8f0fe !important; }

        .close {
            color: rgba(186, 224, 255, 0.7) !important;
            text-shadow: none !important;
        }

        .close:hover { color: #fff !important; }

        /* ── Page heading / breadcrumb ── */
        .page-header h1, .h3 { color: var(--text-bright) !important; }

        /* ── Pagination ── */
        .page-link {
            background: rgba(255,255,255,0.06) !important;
            border-color: rgba(96,165,250,0.2) !important;
            color: #93c5fd !important;
        }
        .page-link:hover {
            background: rgba(37,99,235,0.25) !important;
            color: #fff !important;
        }
        .page-item.active .page-link {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }

        /* ── Scroll-to-top ── */
        .scroll-to-top {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            box-shadow: 0 4px 14px rgba(37,99,235,0.5) !important;
        }

        /* ── DataTables paginate ── */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #93c5fd !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #fff !important;
        }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            color: var(--text-muted-gb) !important;
        }
    </style>

</head>


<body id="page-top">

    <!-- Dark blue background overlay (fixes ::before topbar offset issue in SB Admin 2) -->
    <div class="gb-bg-overlay"></div>

    <div id="wrapper">

        @include('layouts.partials.sidebar')

        <div id="content-wrapper" class="d-flex flex-column">

            <div id="content">

                @include('layouts.partials.topbar')

                <div class="container-fluid">

                    @yield('content')

                </div>

            </div>

            @include('layouts.partials.footer')

        </div>

    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    @include('layouts.partials.logout-modal')

    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/js/demo/datatables-demo.js') }}"></script>
    @stack('scripts')

    <script>
        /**
         * GLASSMORPHISM THEME – MODAL FIX
         * Move ALL Bootstrap .modal elements to <body> so they escape
         * the CSS stacking contexts created by animated sensor cards.
         * (CSS animations + position:relative create stacking contexts
         *  that trap nested modals regardless of their z-index value.)
         */
        $(document).ready(function () {
            // Move every modal to the body root - escapes all stacking contexts
            $('.modal').each(function () {
                if ($(this).parent().is('body') === false) {
                    $(this).appendTo('body');
                }
            });
        });
    </script>

</body>

</html>
