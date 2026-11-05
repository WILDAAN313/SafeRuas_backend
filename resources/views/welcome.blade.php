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
        <section class="auth-art" aria-label="SafeRuas membantu menjaga perjalanan tetap aman">
            <div class="auth-art-copy">
                <span class="auth-art-kicker">SAFE RUAS, AMAN BERKENDARA</span>
                <h1>Perjalanan aman<br>dimulai dari sini.</h1>
                <p>Pantau dan tindak lanjuti laporan infrastruktur jalan bersama SafeRuas.</p>
            </div>
            <div class="auth-animation" id="safeRuasAnimation" role="img" aria-label="Animasi bersepeda"></div>
            <span class="auth-orb auth-orb-one" aria-hidden="true"></span>
            <span class="auth-orb auth-orb-two" aria-hidden="true"></span>
        </section>

        <section class="auth-panel" aria-labelledby="loginHeading">
            <a href="{{ route('home') }}" class="auth-brand" aria-label="SafeRuas, beranda">
                <img src="{{ asset('picture/logo.png') }}" alt="Logo SafeRuas">
                <span>SafeRuas</span>
            </a>

            <div class="auth-heading">
                <span class="auth-eyebrow">PORTAL ADMINISTRASI</span>
                <h2 id="loginHeading">Masuk ke akun Anda</h2>
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
        </section>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.12.2/lottie.min.js" defer></script>
    <script>
        window.addEventListener('load', () => {
            if (window.lottie) {
                window.lottie.loadAnimation({
                    container: document.getElementById('safeRuasAnimation'),
                    renderer: 'svg',
                    loop: true,
                    autoplay: true,
                    path: @json(asset('animations/Bike Riding.json')),
                });
            }
        });
    </script>
</body>
</html>
