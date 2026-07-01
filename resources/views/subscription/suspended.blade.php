@extends('layouts.app')
@section('title', 'Akses Ditangguhkan')

@section('content')
<style>
    .suspend-hero {
        position: relative;
        border-radius: 26px;
        overflow: hidden;
        background: linear-gradient(125deg, #1763c9 0%, #1e3a8a 52%, #0d9488 100%);
        box-shadow: 0 24px 60px rgba(13, 42, 92, .35);
    }
    /* ---- Layer dekoratif berlapis ---- */
    .suspend-hero .blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(2px);
        pointer-events: none;
    }
    .suspend-hero .blob-1 { width: 320px; height: 320px; top: -120px; right: -90px;  background: radial-gradient(circle at 30% 30%, rgba(20,184,166,.55), transparent 70%); }
    .suspend-hero .blob-2 { width: 260px; height: 260px; bottom: -110px; left: -80px; background: radial-gradient(circle at 30% 30%, rgba(37,99,235,.5),  transparent 70%); }
    .suspend-hero .blob-3 { width: 140px; height: 140px; top: 40px; left: 12%;       background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.18), transparent 70%); }
    .suspend-hero .grid-dots {
        position: absolute; inset: 0;
        background-image: radial-gradient(rgba(255,255,255,.14) 1.4px, transparent 1.4px);
        background-size: 22px 22px;
        opacity: .5;
        mask-image: linear-gradient(120deg, #000 0%, transparent 60%);
    }
    .suspend-body { position: relative; z-index: 2; }

    /* ---- Maskot dengan pulse ring ---- */
    .suspend-mascot-wrap { position: relative; display: inline-flex; }
    .suspend-mascot-wrap::before {
        content: ""; position: absolute; inset: auto; left: 50%; top: 58%;
        width: 190px; height: 190px; transform: translate(-50%, -50%);
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,.28), transparent 68%);
        animation: sitara-pulse-ring 2.6s ease-out infinite;
    }
    .suspend-mascot { height: 230px; width: auto; position: relative; z-index: 1;
        filter: drop-shadow(0 22px 34px rgba(6, 24, 58, .45)); }

    /* ---- Chip fitur yang "menanti" ---- */
    .feature-chip {
        display: inline-flex; align-items: center; gap: .45rem;
        background: rgba(255,255,255,.14);
        border: 1px solid rgba(255,255,255,.22);
        color: #fff; font-size: .82rem; font-weight: 500;
        padding: .5rem .85rem; border-radius: 999px;
        backdrop-filter: blur(6px);
    }
    .btn-glow {
        background: #fff; color: #1e3a8a; font-weight: 600; border: none;
        box-shadow: 0 10px 26px rgba(255,255,255,.25);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .btn-glow:hover { transform: translateY(-2px); box-shadow: 0 14px 32px rgba(255,255,255,.4); color: #1d4ed8; }
    .btn-ghost-light {
        border: 1.5px solid rgba(255,255,255,.55); color: #fff; font-weight: 500;
        background: transparent; transition: background .18s ease;
    }
    .btn-ghost-light:hover { background: rgba(255,255,255,.14); color: #fff; }

    @media (max-width: 767px) {
        .suspend-mascot { height: 170px; }
        .suspend-mascot-wrap::before { width: 150px; height: 150px; }
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        <div class="suspend-hero" data-aos="zoom-in">
            <span class="blob blob-1"></span>
            <span class="blob blob-2"></span>
            <span class="blob blob-3"></span>
            <span class="grid-dots"></span>

            <div class="suspend-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    {{-- Maskot --}}
                    <div class="col-md-5 text-center order-md-2">
                        <div class="suspend-mascot-wrap">
                            <img src="{{ asset('assets/maskot4.png') }}" alt="Maskot SITARA"
                                 class="suspend-mascot animate-float">
                        </div>
                    </div>

                    {{-- Pesan --}}
                    <div class="col-md-7 text-white text-center text-md-start order-md-1">
                        <span class="badge rounded-pill mb-3"
                              style="background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);padding:.5rem .9rem;font-weight:500">
                            <i class="bi bi-hourglass-split me-1"></i> Langganan Belum Aktif
                        </span>

                        <h1 class="fw-bold mb-2" style="font-size:1.9rem;line-height:1.2">
                            Sedikit lagi menuju kelas ujian&nbsp;yang lengkap! ✨
                        </h1>

                        <p class="opacity-90 mb-4" style="max-width:36rem">
                            Langganan <strong>{{ auth()->user()->school->name ?? 'sekolah Anda' }}</strong>
                            sedang tidak aktif, jadi fitur ujian dijeda sementara. Aktifkan kembali
                            langganan untuk membuka semua yang sudah menanti di dalam SITARA.
                        </p>

                        {{-- Fitur yang menanti --}}
                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start mb-4">
                            <span class="feature-chip"><i class="bi bi-journal-richtext"></i> Bank Soal</span>
                            <span class="feature-chip"><i class="bi bi-laptop"></i> Ujian Online</span>
                            <span class="feature-chip"><i class="bi bi-graph-up-arrow"></i> Analisis Nilai</span>
                            <span class="feature-chip"><i class="bi bi-shield-check"></i> Ujian Aman</span>
                        </div>

                        {{-- CTA --}}
                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                            @if (auth()->user()->hasRole('admin'))
                                <a href="{{ route('admin.subscription.index') }}" class="btn btn-glow px-4">
                                    <i class="bi bi-rocket-takeoff me-1"></i> Perpanjang Sekarang
                                </a>
                            @else
                                <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="btn btn-glow px-4">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-ghost-light px-4">
                                    <i class="bi bi-box-arrow-right me-1"></i> Keluar
                                </button>
                            </form>
                        </div>

                        <p class="small opacity-75 mt-4 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Butuh bantuan? Hubungi <strong>admin / operator sekolah</strong> Anda untuk memperpanjang langganan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
