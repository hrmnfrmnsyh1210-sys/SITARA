<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · SITARA</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/sitara.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="{{ session('dark_mode') ? 'dark-mode' : '' }}">
<script>
    // Restore desktop sidebar state before paint (avoids flash)
    if (window.innerWidth >= 992 && localStorage.getItem('sitara_sidebar') === 'collapsed') {
        document.body.classList.add('sidebar-collapsed');
    }
</script>
<div class="app-wrapper">
    <div class="sidebar-backdrop" onclick="document.body.classList.remove('sidebar-open')"></div>

    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="sidebar-head">
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="brand text-decoration-none">
                <img src="{{ asset('assets/maskot5.png') }}" alt="SITARA" style="height:42px;width:auto">
                <span>SITARA</span>
            </a>
        </div>
        @include('layouts.partials.sidebar')
        <div class="sidebar-mascot">
            <img src="{{ asset('assets/maskot1.png') }}" alt="">
            <span>Semangat, {{ \Illuminate\Support\Str::limit(auth()->user()->name, 14) }}! 🚀</span>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main-content">
        <nav class="navbar-top">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle" id="sidebarToggle" type="button" title="Buka/tutup menu" aria-label="Buka/tutup menu">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <div class="fw-semibold">@yield('title', 'Dashboard')</div>
                    <small class="text-muted d-none d-md-block">{{ auth()->user()->school->name ?? 'Super Administrator' }}</small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <form method="POST" action="{{ url('/toggle-dark') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-light" title="Mode Gelap">
                        <i class="bi {{ session('dark_mode') ? 'bi-sun' : 'bi-moon-stars' }}"></i>
                    </button>
                </form>

                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="{{ auth()->user()->avatar_url }}" class="avatar-sm">
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><span class="dropdown-item-text small text-muted">{{ ucfirst(str_replace('_',' ',auth()->user()->role)) }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" data-confirm="logout">
                                @csrf
                                <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="content-area">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" data-aos="fade-down">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (auth()->user()->isAdmin() && ! request()->routeIs('admin.subscription.*'))
                @php
                    $subSchool = auth()->user()->school;
                    $schoolInactive = $subSchool && ! $subSchool->is_active;
                    $frozenDays = $schoolInactive && $subSchool->isFrozen() ? $subSchool->frozenRemainingDays() : null;
                    $subActive = $subSchool?->activeSubscription();
                    $subEnds = $subActive?->ends_at;
                    $subDaysLeft = $subEnds ? now()->startOfDay()->diffInDays($subEnds, false) : null;
                @endphp
                @if ($schoolInactive)
                    <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2" role="alert">
                        <span>
                            <i class="bi bi-shield-lock me-2"></i>Akun sekolah <strong>dinonaktifkan</strong> oleh administrator SITARA.
                            Guru &amp; siswa tidak dapat mengakses sistem.
                            @if ($frozenDays)
                                Sisa masa langganan <strong>{{ $frozenDays }} hari</strong> dibekukan &amp; dilanjutkan saat diaktifkan kembali.
                            @endif
                            Silakan hubungi <strong>tim SITARA</strong> untuk mengaktifkan kembali.
                        </span>
                    </div>
                @elseif (! $subActive)
                    <div class="alert alert-danger d-flex align-items-center justify-content-between flex-wrap gap-2" role="alert">
                        <span><i class="bi bi-exclamation-octagon me-2"></i>Langganan sekolah <strong>tidak aktif</strong>. Guru &amp; siswa belum bisa mengikuti ujian.</span>
                        <a href="{{ route('admin.subscription.index') }}" class="btn btn-sm btn-danger">Perpanjang Sekarang</a>
                    </div>
                @elseif ($subDaysLeft !== null && $subDaysLeft <= config('sitara.subscription.warning_days'))
                    <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2" role="alert">
                        <span><i class="bi bi-clock-history me-2"></i>Langganan akan berakhir dalam <strong>{{ (int) $subDaysLeft }} hari</strong> ({{ $subEnds->format('d M Y') }}).</span>
                        <a href="{{ route('admin.subscription.index') }}" class="btn btn-sm btn-warning">Perpanjang</a>
                    </div>
                @endif
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    AOS.init({ duration: 600, once: true });
    window.SITARA_SWAL = {
        deleteImage:  "{{ asset('assets/maskot4.png') }}",
        logoutImage:  "{{ asset('assets/maskot2.png') }}",
        defaultImage: "{{ asset('assets/maskot1.png') }}"
    };
</script>
<script src="{{ asset('js/sitara-confirm.js') }}"></script>
<script src="{{ asset('js/sitara-ui.js') }}"></script>
@if (session('swal'))
    @php $swal = session('swal'); @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: @json($swal['title'] ?? ''),
                text: @json($swal['text'] ?? ''),
                imageUrl: window.SITARA_SWAL.defaultImage,
                imageWidth: 120,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#2563EB',
            });
        });
    </script>
@endif
@stack('scripts')
</body>
</html>
