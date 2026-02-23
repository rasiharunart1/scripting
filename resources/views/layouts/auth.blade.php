<div>
    <!-- We must ship. - Taylor Otwell -->
</div>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    @stack('styles')
    <style>
        :root {
            --soft-bg-top: #f7f9fc;
            --soft-bg-bottom: #f2f6fb;
            --card-bg: #ffffff;
            --card-border: #eef2f7;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --input-border: #d1d5db;
            --ring: 0 0 0 3px rgba(59, 130, 246, .35);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --soft-bg-top: #0b1220;
                --soft-bg-bottom: #111827;
                --card-bg: #0f172a;
                --card-border: #1f2a3a;
                --text-main: #e5e7eb;
                --text-muted: #9ca3af;
                --input-border: #334155;
            }
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(180deg, var(--soft-bg-top) 0%, var(--soft-bg-bottom) 100%);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 60px 20px;
        }

        .card-soft {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 18px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 4px 8px rgba(16, 24, 40, .03), 0 10px 30px rgba(16, 24, 40, .06);
            position: relative;
            z-index: 2;
        }

        .card-soft .title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text-main);
            margin: .4rem 0 .2rem;
            letter-spacing: .3px;
            text-align: center;
        }

        .card-soft .subtitle {
            font-size: .92rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 1.4rem;
        }

        .logo-wrap {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            background: linear-gradient(135deg, #eef2ff 0%, #e6f2ff 100%);
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-group+.form-group {
            margin-top: 14px;
        }

        .label {
            font-size: .74rem;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: .5px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 6px;
        }

        .input-soft {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--input-border);
            border-radius: 12px;
            background: #fff;
            font-size: .92rem;
            transition: .18s ease;
        }

        .input-soft:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: var(--ring);
        }

        @media (prefers-color-scheme: dark) {
            .input-soft {
                background: #0b1220;
                color: #e5e7eb;
            }
        }

        .btn-primary-soft {
            border: none;
            width: 100%;
            padding: 12px 18px;
            border-radius: 14px;
            font-weight: 600;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 8px 18px -6px rgba(37, 99, 235, .45);
            transition: .22s ease;
        }

        .btn-primary-soft:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px -4px rgba(37, 99, 235, .55);
        }

        .btn-google-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 16px;
            background: #ffffff;
            color: #111827;
            font-weight: 600;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            transition: .2s ease;
            text-decoration: none;
            font-size: .9rem;
        }

        .btn-google-soft:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(16, 24, 40, .08);
            border-color: #d1d5db;
        }

        @media (prefers-color-scheme: dark) {
            .btn-google-soft {
                background: #0b1220;
                color: #e5e7eb;
                border-color: #1f2a3a;
            }

            .btn-google-soft:hover {
                border-color: #334155;
                box-shadow: 0 8px 20px rgba(0, 0, 0, .45);
            }
        }

        .btn-google-soft i {
            color: #ea4335;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0 10px;
        }

        .divider span {
            font-size: .65rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--text-muted);
        }

        .divider:before,
        .divider:after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--card-border);
        }

        .error-msg {
            font-size: .7rem;
            color: #dc2626;
            margin-top: 4px;
            font-weight: 500;
        }

        .extra-links {
            text-align: center;
            margin-top: 18px;
            font-size: .8rem;
            color: var(--text-muted);
        }

        .extra-links a {
            text-decoration: none;
            font-weight: 600;
        }

        .top-right-link {
            position: absolute;
            right: 0;
            top: 0;
            padding: 10px 14px;
            font-size: .7rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-muted);
        }

        .bg-blob {
            position: absolute;
            filter: blur(42px);
            opacity: .35;
            z-index: 0;
            pointer-events: none;
        }

        .bg-blob-1 {
            width: 320px;
            height: 320px;
            background: #dbeafe;
            top: -70px;
            right: -40px;
            border-radius: 50%;
        }

        .bg-blob-2 {
            width: 260px;
            height: 260px;
            background: #fde68a;
            bottom: -60px;
            left: -60px;
            border-radius: 50%;
        }

        .fade-in {
            animation: fadeIn .55s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 520px) {
            .card-soft {
                border-radius: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        @yield('content')
        <div class="bg-blob bg-blob-1"></div>
        <div class="bg-blob bg-blob-2"></div>
    </div>
    @stack('scripts')
</body>

</html>
