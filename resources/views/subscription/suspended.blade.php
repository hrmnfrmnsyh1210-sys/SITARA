@extends('layouts.app')
@section('title', 'Akses Ditangguhkan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card text-center" data-aos="fade-up">
            <div class="card-body p-5">
                <span class="stat-icon bg-soft-warning mx-auto mb-3" style="width:72px;height:72px;font-size:2rem">
                    <i class="bi bi-lock"></i>
                </span>
                <h1 class="page-title mb-2">Akses Sementara Ditangguhkan</h1>
                <p class="text-muted mb-4">
                    Langganan {{ auth()->user()->school->name ?? 'sekolah Anda' }} sedang tidak aktif,
                    sehingga fitur ujian belum bisa diakses. Silakan hubungi
                    <strong>admin/operator sekolah</strong> untuk memperpanjang langganan.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="btn btn-light">
                        <i class="bi bi-arrow-left me-1"></i>Coba Lagi
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-primary"><i class="bi bi-box-arrow-right me-1"></i>Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
