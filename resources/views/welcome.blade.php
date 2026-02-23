<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'MDPOWER') }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-top: #f7f9fc;
            --bg-bottom: #f2f6fb;
            --card-bg: #ffffff;
            --card-border: #eef2f7;
            --text: #1f2937;
            --text-muted: #6b7280;
            --primary-start: #2563eb;
            --primary-end: #1d4ed8;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-top: #0b1220;
                --bg-bottom: #111827;
                --card-bg: #0f172a;
                --card-border: #1f2a3a;
                --text: #e5e7eb;
                --text-muted: #94a3b8;
            }
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(180deg, var(--bg-top) 0%, var(--bg-bottom) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .wrap {
            position: relative;
            width: 100%;
            max-width: 400px;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 42px 38px 40px;
            text-align: center;
            box-shadow: 0 10px 28px -10px rgba(16, 24, 40, 0.15),
                0 4px 10px -4px rgba(16, 24, 40, 0.08);
            position: relative;
            z-index: 2;
        }

        .logo {
            width: 70px;
            height: 70px;
            margin: 0 auto 18px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff, #e6f2ff);
        }

        .logo img {
            max-width: 50px;
            display: block;
        }

        h1 {
            font-size: 1.25rem;
            margin: 0 0 6px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        p.desc {
            margin: 0 0 28px;
            font-size: .82rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn {
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: .82rem;
            padding: 13px 16px;
            border-radius: 14px;
            transition: .22s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            letter-spacing: .3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-start), var(--primary-end));
            color: #fff;
            box-shadow: 0 10px 20px -8px rgba(37, 99, 235, 0.55);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px -8px rgba(37, 99, 235, 0.65);
        }

        .btn-muted {
            background: #ffffff;
            color: #1f2937;
            border: 1px solid #e5e7eb;
        }

        .btn-muted:hover {
            box-shadow: 0 8px 20px -10px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        @media (prefers-color-scheme: dark) {
            .btn-muted {
                background: #0b1220;
                color: #e5e7eb;
                border: 1px solid #1f2a3a;
            }

            .btn-muted:hover {
                border-color: #334155;
                box-shadow: 0 8px 24px -8px rgba(0, 0, 0, 0.55);
            }
        }

        .foot {
            margin-top: 30px;
            font-size: .65rem;
            color: var(--text-muted);
            letter-spacing: .4px;
        }

        /* Decorative blobs (halus) */
        .blob {
            position: absolute;
            filter: blur(50px);
            opacity: .35;
            z-index: 0;
            pointer-events: none;
        }

        .blob-a {
            width: 240px;
            height: 240px;
            background: #dbeafe;
            top: -70px;
            right: -60px;
            border-radius: 50%;
        }

        .blob-b {
            width: 220px;
            height: 220px;
            background: #fde68a;
            bottom: -70px;
            left: -60px;
            border-radius: 50%;
        }

        @media (max-width:480px) {
            .card {
                padding: 36px 28px 34px;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="card">
            <div class="logo">
                <img src="{{ asset('assets/img/l.png') }}" alt="Logo">
            </div>
            <h1>MEGADATA POWERPLANT</h1>
            <p class="desc">Realtime Solar Panel Monitoring</p>

            @auth
                <div class="actions">
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary">Masuk Dashboard</a>
                    {{-- (Opsional) Logout cepat --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-muted w-100" type="submit">Logout</button>
                    </form>
                </div>
            @else
                <div class="actions">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                    @endif
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-muted">Register</a>
                    @endif
                </div>
            @endauth

            <div class="foot">
                &copy; {{ date('Y') }} {{ config('app.name', 'MDPOWER') }}
            </div>
        </div>

        <div class="blob blob-a"></div>
        <div class="blob blob-b"></div>
    </div>
</body>

</html>
