<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    @stack('styles')
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }

        :root {
            --blue-900: #020c24;
            --blue-800: #071535;
            --blue-700: #0a1f55;
            --blue-600: #0d2a7a;
            --blue-500: #1a3fa8;
            --blue-400: #2563eb;
            --blue-300: #3d7eff;
            --blue-200: #60a5fa;
            --blue-100: #bae0ff;
            --glass-bg: rgba(255, 255, 255, 0.055);
            --glass-border: rgba(96, 165, 250, 0.2);
            --glass-inner: rgba(255, 255, 255, 0.04);
            --glow-blue: rgba(37, 99, 235, 0.55);
            --text-main: #e8f0fe;
            --text-muted: rgba(186, 224, 255, 0.65);
            --input-bg: rgba(255, 255, 255, 0.07);
            --input-border: rgba(96, 165, 250, 0.25);
            --input-focus: rgba(96, 165, 250, 0.55);
            --ring: 0 0 0 3px rgba(59, 130, 246, .28);
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100%;
        }

        body {
            background: radial-gradient(ellipse at 70% 20%, #0d2472 0%, #061030 45%, #020817 100%);
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* ===== Animated background ===== */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(13, 42, 114, 0.45) 0%, transparent 50%),
                radial-gradient(circle at 80% 10%, rgba(26, 63, 168, 0.3) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(7, 21, 53, 0.6) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* ===== Stars / particle dots ===== */
        .stars {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .star {
            position: absolute;
            border-radius: 50%;
            background: rgba(147, 197, 253, 0.7);
            animation: twinkle var(--dur, 4s) ease-in-out infinite var(--delay, 0s);
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.4); }
        }

        /* ===== Blobs ===== */
        .bg-blob {
            position: fixed;
            filter: blur(80px);
            opacity: 0.22;
            z-index: 0;
            pointer-events: none;
            border-radius: 50%;
            animation: blobFloat var(--dur2, 12s) ease-in-out infinite alternate;
        }

        @keyframes blobFloat {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(30px, -20px) scale(1.08); }
        }

        .bg-blob-1 {
            width: 420px; height: 420px;
            background: radial-gradient(circle, #1d4ed8, #1e40af);
            top: -100px; right: -80px;
            --dur2: 14s;
        }

        .bg-blob-2 {
            width: 340px; height: 340px;
            background: radial-gradient(circle, #0ea5e9, #0369a1);
            bottom: -80px; left: -80px;
            --dur2: 18s;
        }

        .bg-blob-3 {
            width: 240px; height: 240px;
            background: radial-gradient(circle, #4338ca, #1e1b4b);
            top: 40%; left: 40%;
            --dur2: 22s;
        }

        /* ===== Auth wrapper ===== */
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            z-index: 1;
            padding: 60px 20px;
        }

        /* ===== Glassmorphism card ===== */
        .card-soft {
            background: var(--glass-bg);
            backdrop-filter: blur(28px) saturate(160%);
            -webkit-backdrop-filter: blur(28px) saturate(160%);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            width: 100%;
            max-width: 470px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.45),
                0 0 0 1px rgba(255, 255, 255, 0.05) inset,
                0 1px 0 rgba(255, 255, 255, 0.1) inset;
            position: relative;
            z-index: 2;
            animation: fadeSlideIn .55s cubic-bezier(.22,.68,0,1.2);
            overflow: hidden;
        }

        /* subtle top glow line */
        .card-soft::before {
            content: '';
            position: absolute;
            top: 0; left: 10%; right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(96, 165, 250, 0.6), transparent);
        }

        /* inner glass shine */
        .card-soft::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 50%;
            background: linear-gradient(180deg, rgba(255,255,255,0.06) 0%, transparent 100%);
            border-radius: 24px 24px 0 0;
            pointer-events: none;
        }

        .card-soft .title {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-main);
            margin: .4rem 0 .25rem;
            letter-spacing: .3px;
            text-align: center;
            text-shadow: 0 0 20px rgba(96, 165, 250, 0.4);
        }

        .card-soft .subtitle {
            font-size: .88rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        /* ===== Logo wrap ===== */
        .logo-wrap {
            width: 76px;
            height: 76px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.35), rgba(14, 165, 233, 0.25));
            border: 1px solid rgba(96, 165, 250, 0.3);
            box-shadow: 0 0 24px rgba(37, 99, 235, 0.35), 0 4px 12px rgba(0,0,0,0.3);
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
        }

        /* ===== Form groups ===== */
        .form-group + .form-group {
            margin-top: 14px;
        }

        .label {
            font-size: .72rem;
            font-weight: 600;
            color: var(--blue-200);
            letter-spacing: .8px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 7px;
        }

        .input-soft {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--input-border);
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--text-main);
            font-size: .92rem;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
            outline: none;
        }

        .input-soft::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }

        .input-soft:focus {
            border-color: var(--input-focus);
            background: rgba(255, 255, 255, 0.10);
            box-shadow: var(--ring), 0 0 18px rgba(59, 130, 246, .15);
        }

        /* ===== Primary button ===== */
        .btn-primary-soft {
            border: none;
            width: 100%;
            padding: 13px 18px;
            border-radius: 14px;
            font-weight: 700;
            font-size: .95rem;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            box-shadow:
                0 6px 20px rgba(37, 99, 235, 0.5),
                0 0 0 1px rgba(99, 160, 255, 0.2) inset;
            transition: transform .2s ease, box-shadow .2s ease;
            letter-spacing: .2px;
        }

        .btn-primary-soft:hover {
            transform: translateY(-2px);
            box-shadow:
                0 10px 28px rgba(37, 99, 235, 0.65),
                0 0 0 1px rgba(99, 160, 255, 0.3) inset;
        }

        .btn-primary-soft:active {
            transform: translateY(0);
        }

        /* ===== Google button ===== */
        .btn-google-soft {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.08);
            color: var(--text-main);
            font-weight: 600;
            border: 1px solid rgba(96, 165, 250, 0.2);
            border-radius: 12px;
            transition: .2s ease;
            text-decoration: none;
            font-size: .9rem;
            font-family: 'Inter', sans-serif;
            backdrop-filter: blur(8px);
        }

        .btn-google-soft:hover {
            transform: translateY(-1px);
            background: rgba(255,255,255,0.12);
            border-color: rgba(96, 165, 250, 0.4);
            box-shadow: 0 6px 18px rgba(0,0,0,0.3);
            color: var(--text-main);
        }

        .btn-google-soft i {
            color: #ea4335;
        }

        /* ===== Divider ===== */
        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 20px 0 10px;
        }

        .divider span {
            font-size: .62rem;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .divider:before, .divider:after {
            content: "";
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(96, 165, 250, 0.25), transparent);
        }

        /* ===== Error message ===== */
        .error-msg {
            font-size: .69rem;
            color: #f87171;
            margin-top: 5px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ===== Extra links ===== */
        .extra-links {
            text-align: center;
            margin-top: 18px;
            font-size: .8rem;
            color: var(--text-muted);
        }

        .extra-links a {
            text-decoration: none;
            font-weight: 600;
            color: var(--blue-200);
            transition: color .15s, text-shadow .15s;
        }

        .extra-links a:hover {
            color: #fff;
            text-shadow: 0 0 12px rgba(96, 165, 250, 0.7);
        }

        /* ===== Top-right link ===== */
        .top-right-link {
            position: absolute;
            right: 0;
            top: 0;
            padding: 12px 16px;
            font-size: .72rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--text-muted);
            z-index: 3;
            transition: color .15s;
        }

        .top-right-link:hover {
            color: var(--blue-200);
        }

        /* ===== Alert ===== */
        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: .78rem;
            margin-bottom: 14px;
        }

        /* ===== Fade animation ===== */
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(18px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .fade-in {
            animation: fadeSlideIn .55s cubic-bezier(.22,.68,0,1.2);
        }

        /* ===== Responsive ===== */
        @media (max-width: 520px) {
            .card-soft {
                border-radius: 18px;
            }

            .auth-wrapper {
                padding: 30px 14px;
            }
        }
    </style>
</head>

<body>
    <!-- Animated stars -->
    <div class="stars" id="stars-container"></div>

    <!-- Background blobs -->
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>

    <div class="auth-wrapper">
        @yield('content')
    </div>

    @stack('scripts')

    <script>
        // Generate twinkling star particles
        (function() {
            const container = document.getElementById('stars-container');
            const count = 80;
            for (let i = 0; i < count; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                const size = Math.random() * 2.5 + 0.5;
                star.style.cssText = `
                    width: ${size}px;
                    height: ${size}px;
                    top: ${Math.random() * 100}%;
                    left: ${Math.random() * 100}%;
                    --dur: ${Math.random() * 4 + 3}s;
                    --delay: ${Math.random() * -6}s;
                    opacity: ${Math.random() * 0.5 + 0.1};
                `;
                container.appendChild(star);
            }
        })();
    </script>
</body>

</html>
