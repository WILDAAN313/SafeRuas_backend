<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk Admin Instansi | SafeRuas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-card">
        <a href="{{ route('home') }}" class="auth-brand" aria-label="SafeRuas">
            <span class="auth-brand-mark" aria-hidden="true">S</span>
            <span>SafeRuas</span>
        </a>

        <div class="auth-heading">
            <span class="auth-eyebrow">PORTAL ADMINISTRASI</span>
            <h1>Masuk ke akun Anda</h1>
            <p>Gunakan akun admin instansi untuk mengelola laporan infrastruktur.</p>
        </div>

        @if ($errors->any())
            <div class="auth-alert" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="auth-field">
                <label for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="username"
                    required
                    autofocus
                    placeholder="nama@instansi.go.id"
                >
            </div>

            <div class="auth-field">
                <label for="password">Kata sandi</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="Masukkan kata sandi"
                >
            </div>

            <label class="auth-remember">
                <input type="checkbox" name="remember" value="1">
                <span>Ingat saya</span>
            </label>

            <button type="submit" class="auth-submit">Masuk</button>
        </form>

        <p class="auth-footnote">Akses hanya untuk admin instansi SafeRuas.</p>
    </main>
</body>
</html>
