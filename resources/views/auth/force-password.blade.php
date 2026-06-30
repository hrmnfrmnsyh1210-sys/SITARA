<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti Kata Sandi · SITARA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/sitara.css') }}" rel="stylesheet">
    <style>
        body {
            min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; margin: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #5eead4 0%, #2dd4bf 45%, #14b8a6 100%);
            background-attachment: fixed;
        }
        .pw-card {
            width: 100%; max-width: 440px; background: #fff; border-radius: 24px;
            box-shadow: 0 40px 90px rgba(8,30,70,.30); padding: 2.5rem 2.25rem;
            animation: pop .5s ease both;
        }
        @keyframes pop { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .pw-card .icon-badge {
            width: 64px; height: 64px; border-radius: 18px; display: grid; place-items: center;
            background: linear-gradient(135deg, #38d6ff, #2dd4bf); color: #06222b; font-size: 1.8rem;
            margin: 0 auto 1.25rem; box-shadow: 0 12px 26px rgba(45,212,191,.4);
        }
        .pw-card h1 { font-size: 1.4rem; font-weight: 800; text-align: center; color: #0f2a52; margin: 0 0 .4rem; }
        .pw-card .lead-sub { text-align: center; color: #64748b; font-size: .92rem; margin-bottom: 1.75rem; }
        .pw-card .btn-save {
            width: 100%; border: 0; border-radius: 12px; padding: .8rem; font-weight: 700; color: #06222b;
            background: linear-gradient(135deg, #38d6ff 0%, #2dd4bf 100%);
            box-shadow: 0 12px 26px rgba(45,212,191,.35); transition: transform .15s, filter .2s;
        }
        .pw-card .btn-save:hover { transform: translateY(-2px); filter: brightness(1.04); }
    </style>
</head>
<body>
    <div class="pw-card">
        <div class="icon-badge"><i class="bi bi-shield-lock"></i></div>
        <h1>Amankan Akun Anda</h1>
        <p class="lead-sub">Ini login pertama Anda. Demi keamanan, silakan ganti kata sandi default sebelum melanjutkan.</p>

        @if ($errors->any())
            <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label fw-semibold small">Kata Sandi Baru</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required autofocus>
                    <span class="input-group-text bg-light toggle-pass" style="cursor:pointer"><i class="bi bi-eye" id="eye1"></i></span>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold small">Konfirmasi Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru" required>
                </div>
            </div>
            <button class="btn-save"><i class="bi bi-check-lg me-1"></i>Simpan & Lanjutkan</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center mt-3">
            @csrf
            <button class="btn btn-link text-muted small text-decoration-none"><i class="bi bi-box-arrow-left me-1"></i>Keluar</button>
        </form>
    </div>

    <script>
        document.querySelector('.toggle-pass')?.addEventListener('click', () => {
            const p = document.getElementById('password'), e = document.getElementById('eye1');
            const show = p.type === 'password';
            p.type = show ? 'text' : 'password';
            e.classList.toggle('bi-eye', !show);
            e.classList.toggle('bi-eye-slash', show);
        });
    </script>
</body>
</html>
