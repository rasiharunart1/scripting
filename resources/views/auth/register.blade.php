@extends('layouts.auth')

@section('content')
    <div class="card-soft fade-in">
        <a href="{{ route('login') }}" class="top-right-link d-none d-md-inline">Masuk</a>
        <div class="p-4 p-md-5">

            <div class="logo-wrap">
                <img src="{{ asset('assets/img/l.png') }}" alt="Logo" style="max-width:56px;">
            </div>

            <h2 class="title">Buat Akun</h2>
            <div class="subtitle">Monitoring PLTS – mulai sekarang.</div>



            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label class="label" for="name">Nama</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="input-soft @error('name') is-invalid @enderror" placeholder="Nama lengkap">
                    @error('name')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        class="input-soft @error('email') is-invalid @enderror" placeholder="you@example.com">
                    @error('email')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="label" for="password">Password</label>
                    <input id="password" type="password" name="password" required
                        class="input-soft @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter">
                    @error('password')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="label" for="password_confirmation">Konfirmasi Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="input-soft" placeholder="Ulangi password">
                </div>

                <div style="margin-top:24px;">
                    <button type="submit" class="btn-primary-soft">
                        Daftar
                    </button>
                </div>
            </form>

            <div class="extra-links">
                Sudah punya akun?
                @if (Route::has('login'))
                    <a href="{{ route('login') }}">Masuk</a>
                @endif
            </div>
        </div>
    </div>
@endsection
