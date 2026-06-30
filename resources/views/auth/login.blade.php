@php
    $hour = (int) now()->format('H');
    if     ($hour < 11) { $greet = 'Selamat Pagi';  $sub = 'Semangat menyambut hari belajarmu!'; }
    elseif ($hour < 15) { $greet = 'Selamat Siang'; $sub = 'Tetap fokus, kamu pasti bisa!'; }
    elseif ($hour < 19) { $greet = 'Selamat Sore';  $sub = 'Satu langkah lagi menuju sukses!'; }
    else                { $greet = 'Selamat Malam'; $sub = 'Waktunya raih hasil terbaikmu!'; }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · SITARA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/sitara.css') }}" rel="stylesheet">
    <style>
        body {
            min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; margin: 0;
            background:
                radial-gradient(120% 120% at 0% 0%, rgba(255,255,255,.85) 0%, rgba(255,255,255,0) 45%),
                radial-gradient(110% 110% at 100% 0%, rgba(96,165,250,.55) 0%, rgba(96,165,250,0) 50%),
                radial-gradient(120% 120% at 100% 100%, rgba(37,99,235,.45) 0%, rgba(37,99,235,0) 55%),
                linear-gradient(135deg, #5eead4 0%, #2dd4bf 45%, #14b8a6 100%);
            background-attachment: fixed;
        }
        /* soft floating blobs behind the card — adds layered depth */
        body::before, body::after {
            content: ""; position: fixed; border-radius: 50%; filter: blur(10px); z-index: 0; opacity: .55;
        }
        body::before { width: 340px; height: 340px; top: -100px; left: -80px;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.9), transparent 70%); }
        body::after  { width: 420px; height: 420px; bottom: -130px; right: -100px;
            background: radial-gradient(circle at 70% 70%, rgba(59,130,246,.7), transparent 70%); }

        .login-shell {
            position: relative; z-index: 1;
            display: grid; grid-template-columns: 1.05fr .95fr;
            width: 100%; max-width: 940px; min-height: 540px;
            background: rgba(255,255,255,.78); backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,.6);
            border-radius: 28px; overflow: hidden;
            box-shadow: 0 40px 90px rgba(8,30,70,.30);
            animation: sitara-pop .6s ease both;
        }

        /* ---- Left: greeting + mascot ---- */
        .login-hero {
            position: relative; padding: 3rem 2.75rem;
            display: flex; flex-direction: column; justify-content: center;
            overflow: hidden;
        }
        .login-hero .greet {
            font-size: clamp(2rem, 4vw, 2.9rem); font-weight: 800; line-height: 1.1;
            color: #0f2a52; letter-spacing: -.02em; margin: 0;
        }
        .login-hero .greet .wave { display: inline-block; transform-origin: 70% 70%;
            animation: sitara-wave 2.4s ease-in-out infinite; }
        @keyframes sitara-wave { 0%,60%,100% { transform: rotate(0); } 70% { transform: rotate(16deg); } 85% { transform: rotate(-8deg); } }
        .login-hero .greet-sub { margin-top: .6rem; color: #3f5d86; font-size: 1.02rem; font-weight: 500; }
        .login-hero .mascot {
            margin-top: 1.25rem; align-self: center; width: min(78%, 290px);
            filter: drop-shadow(0 22px 28px rgba(12,40,90,.22));
            animation: sitara-float 5s ease-in-out infinite;
        }
        .login-hero .brand-chip {
            display: inline-flex; align-items: center; gap: .55rem;
            font-weight: 700; color: #0f2a52; letter-spacing: .04em; margin-bottom: 1.25rem;
        }
        .login-hero .brand-chip img { height: 38px; width: auto; border-radius: 10px; }

        /* ---- Right: form panel ---- */
        .login-form-panel {
            position: relative; padding: 3rem 2.75rem; color: #e7eefc;
            display: flex; flex-direction: column; justify-content: center;
            background: linear-gradient(195deg, #0b2447 0%, #103a6b 55%, #0c4d50 100%);
        }
        .login-form-panel h2 { font-weight: 800; font-size: 1.5rem; color: #fff; margin: 0 0 .35rem; }
        .login-form-panel .panel-sub { color: #9fb6db; font-size: .9rem; margin-bottom: 1.75rem; }
        .login-form-panel .form-label {
            text-transform: uppercase; letter-spacing: .08em; font-size: .7rem;
            font-weight: 600; color: #9fb6db; margin-bottom: .4rem;
        }
        .login-form-panel .input-pill {
            display: flex; align-items: center; gap: .6rem;
            background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.14);
            border-radius: 999px; padding: .15rem .9rem; transition: border-color .2s, background .2s, box-shadow .2s;
        }
        .login-form-panel .input-pill:focus-within {
            border-color: #2dd4bf; background: rgba(255,255,255,.16);
            box-shadow: 0 0 0 4px rgba(45,212,191,.18);
        }
        .login-form-panel .input-pill i { color: #8fb0d6; font-size: 1rem; }
        .login-form-panel .input-pill input {
            flex: 1; background: transparent; border: 0; outline: none; color: #fff;
            padding: .7rem 0; font-size: .95rem;
        }
        .login-form-panel .input-pill input::placeholder { color: #7e98bf; }
        .login-form-panel .input-pill .toggle-pass { cursor: pointer; }
        /* kill the white autofill background so the pill stays transparent */
        .login-form-panel .input-pill input:-webkit-autofill,
        .login-form-panel .input-pill input:-webkit-autofill:hover,
        .login-form-panel .input-pill input:-webkit-autofill:focus,
        .login-form-panel .input-pill input:-webkit-autofill:active {
            -webkit-text-fill-color: #fff;
            caret-color: #fff;
            transition: background-color 9999s ease-in-out 0s;
            box-shadow: 0 0 0 1000px transparent inset !important;
            -webkit-box-shadow: 0 0 0 1000px transparent inset !important;
        }

        .login-form-panel .form-check-input {
            background-color: rgba(255,255,255,.12); border-color: rgba(255,255,255,.3);
        }
        .login-form-panel .form-check-input:checked { background-color: #14b8a6; border-color: #14b8a6; }
        .login-form-panel .form-check-label { color: #c4d4ee; font-size: .85rem; }

        .btn-signin {
            width: 100%; border: 0; border-radius: 999px; padding: .8rem; margin-top: .25rem;
            font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #06222b; font-size: .85rem;
            background: linear-gradient(135deg, #38d6ff 0%, #2dd4bf 100%);
            box-shadow: 0 12px 26px rgba(45,212,191,.35); transition: transform .15s, box-shadow .2s, filter .2s;
        }
        .btn-signin:hover { transform: translateY(-2px); box-shadow: 0 16px 32px rgba(45,212,191,.5); filter: brightness(1.04); }
        .btn-signin:active { transform: translateY(0); }

        .login-form-panel .back-link { color: #9fb6db; font-size: .82rem; text-decoration: none; transition: color .15s; }
        .login-form-panel .back-link:hover { color: #fff; }

        .login-alert {
            background: rgba(248,113,113,.14); border: 1px solid rgba(248,113,113,.4);
            color: #fecaca; border-radius: 14px; padding: .6rem .85rem; font-size: .82rem; margin-bottom: 1.1rem;
        }

        /* ---- Responsive: stack, keep mascot ---- */
        @media (max-width: 820px) {
            .login-shell { grid-template-columns: 1fr; max-width: 440px; min-height: 0; }
            .login-hero { padding: 2.25rem 2rem 1rem; text-align: center; align-items: center; }
            .login-hero .brand-chip { margin: 0 auto 1rem; }
            .login-hero .mascot { width: 180px; margin-top: .5rem; }
            .login-form-panel { padding: 2rem; }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <!-- Left: greeting + mascot -->
        <div class="login-hero">
            <span class="brand-chip">
                <img src="{{ asset('assets/logo.png') }}" alt="SITARA"> SITARA
            </span>
            <h1 class="greet">{{ $greet }} <span class="wave">👋</span></h1>
            <p class="greet-sub">{{ $sub }}</p>
            <img src="{{ asset('assets/maskot5.png') }}" alt="Maskot SITARA" class="mascot">
        </div>

        <!-- Right: form -->
        <div class="login-form-panel">
            <h2>Masuk ke Akun</h2>
            <p class="panel-sub">Sistem Tes Akademik Terpadu</p>

            @if ($errors->any())
                <div class="login-alert"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email / Username / NIS</label>
                    <div class="input-pill">
                        <i class="bi bi-person"></i>
                        <input type="text" name="login" value="{{ old('login') }}" placeholder="Masukkan kredensial" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kata Sandi</label>
                    <div class="input-pill">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                        <i class="bi bi-eye toggle-pass" id="togglePass"></i>
                    </div>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <button class="btn-signin"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('landing') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Kembali ke beranda</a>
            </div>
        </div>
    </div>

    <script>
        const toggle = document.getElementById('togglePass');
        const pass = document.getElementById('password');
        toggle.addEventListener('click', () => {
            const show = pass.type === 'password';
            pass.type = show ? 'text' : 'password';
            toggle.classList.toggle('bi-eye', !show);
            toggle.classList.toggle('bi-eye-slash', show);
        });
    </script>
</body>
</html>
