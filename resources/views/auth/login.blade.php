@extends('layouts.auth')

@section('content')
    <div class="card-soft fade-in">
        {{-- <a href="{{ route('register') }}" class="top-right-link d-none d-md-inline">Daftar</a> --}}
        <div class="p-4 p-md-5">

            <div class="logo-wrap">
                <img src="{{ asset('assets/img/l.png') }}" alt="Logo" style="max-width:56px;">
            </div>

            <h2 class="title">MEGADATA POWERPLANT</h2>
            <div class="subtitle">Realtime Solar Panel Monitoring.</div>

            @if (session('status'))
                <div class="alert alert-success py-2 px-3 mb-3" style="font-size:.75rem;">
                    {{ session('status') }}
                </div>
            @endif

            {{-- <a href="{{ url('auth/google') }}" class="btn-google-soft mb-3">
                <i class="fab fa-google"></i>
                <span>Masuk dengan Google</span>
            </a> --}}

            {{-- <div class="divider">
                <span>atau login manual</span>
            </div> --}}

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label class="label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus required
                        class="input-soft @error('email') is-invalid @enderror" placeholder="you@example.com">
                    @error('email')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">

                    <input id="password" type="password" name="password" required
                        class="input-soft @error('password') is-invalid @enderror" placeholder="••••••••">
                    @error('password')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-top:10px;">
                    <label
                        style="display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:500;color:var(--text-muted);">
                        <input type="checkbox" name="remember"
                            style="width:15px;height:15px;border:1px solid var(--input-border);border-radius:4px;">
                        Ingat saya
                    </label>
                </div>

                <div style="margin-top:22px;">
                    <button class="btn-primary-soft" type="submit">
                        Masuk
                    </button>
                </div>
            </form>

            <div class="extra-links">
                Belum punya akun?
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Daftar</a>
                @endif
            </div>
            <div class="extra-links">
                Lupa Password?
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Reset Password</a>
                @endif
            </div>
        </div>
    </div>
@endsection
