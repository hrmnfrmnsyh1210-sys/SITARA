@extends('layouts.app')
@section('title', 'Konfirmasi Ujian')

@php
    // Lokasi hanya diminta sekali: attempt yang sudah merekamnya tidak ditanya lagi saat melanjutkan.
    $needsLocation = $schedule->requires_location && ! ($result && $result->hasLocation());
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7" data-aos="fade-up">
        <div class="card">
            <div class="card-body p-4 p-md-5 text-center">
                <img src="{{ asset('assets/maskot4.png') }}" alt="" class="mb-2" style="height:110px;width:auto">
                <div></div>
                <h3 class="fw-bold">{{ $schedule->exam->title }}</h3>
                <p class="text-muted">{{ $schedule->exam->subject->name ?? '' }}</p>

                <div class="row g-3 text-start my-4">
                    <div class="col-6"><div class="border rounded p-3"><small class="text-muted d-block">Durasi</small><span class="fw-bold"><i class="bi bi-clock me-1"></i>{{ $schedule->exam->duration_minutes }} menit</span></div></div>
                    <div class="col-6"><div class="border rounded p-3"><small class="text-muted d-block">Jumlah Soal</small><span class="fw-bold"><i class="bi bi-list-ol me-1"></i>{{ $questionCount }} soal</span></div></div>
                    <div class="col-6"><div class="border rounded p-3"><small class="text-muted d-block">Nilai Minimum</small><span class="fw-bold"><i class="bi bi-award me-1"></i>{{ $schedule->exam->passing_score }}</span></div></div>
                    <div class="col-6"><div class="border rounded p-3"><small class="text-muted d-block">Berakhir</small><span class="fw-bold"><i class="bi bi-calendar me-1"></i>{{ $schedule->end_time->format('H:i') }}</span></div></div>
                </div>

                @if($schedule->exam->description)
                    <div class="alert alert-light text-start"><strong>Petunjuk:</strong><br>{!! nl2br(e($schedule->exam->description)) !!}</div>
                @endif

                <div class="alert alert-warning text-start small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Perhatian:</strong> Setelah memulai, timer berjalan otomatis. Jawaban tersimpan otomatis. Jangan menutup browser. Ujian akan dikumpulkan otomatis saat waktu habis.
                </div>

                <form method="POST" action="{{ route('siswa.exams.start',$schedule) }}" id="startForm">
                    @csrf

                    @if($needsLocation)
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="location_accuracy" id="locationAccuracy">

                        <div class="border rounded p-3 text-start mb-3" id="locationBox">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-geo-alt-fill fs-4 text-primary"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Verifikasi Lokasi</div>
                                    <div class="small text-muted" id="locationStatus">
                                        Ujian ini mewajibkan pengiriman lokasi. Klik tombol di bawah, lalu pilih <b>Izinkan</b> saat browser meminta akses lokasi.
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-3" id="btnLocation">
                                <i class="bi bi-crosshair me-1"></i>Kirim Lokasi Saya
                            </button>
                        </div>
                    @endif

                    <a href="{{ route('siswa.exams.index') }}" class="btn btn-light px-4">Batal</a>
                    <button class="btn btn-primary px-4" id="btnStart" @disabled($needsLocation)>
                        {{ $result ? 'Lanjutkan Ujian' : 'Mulai Ujian' }} <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@if($needsLocation)
@push('scripts')
<script>
(function () {
    const btnLocation = document.getElementById('btnLocation');
    const btnStart    = document.getElementById('btnStart');
    const status      = document.getElementById('locationStatus');
    const box         = document.getElementById('locationBox');

    // Geolocation API hanya jalan di HTTPS (atau localhost saat development).
    const isSecure = window.isSecureContext;

    function setStatus(html, cls) {
        status.innerHTML = html;
        box.className = 'border rounded p-3 text-start mb-3 ' + (cls || '');
    }

    if (!isSecure || !navigator.geolocation) {
        btnLocation.disabled = true;
        setStatus(
            'Browser Anda tidak dapat mengakses lokasi karena situs tidak diakses lewat koneksi aman (HTTPS). ' +
            'Hubungi pengawas ujian.',
            'border-danger text-danger'
        );
        return;
    }

    btnLocation.addEventListener('click', function () {
        btnLocation.disabled = true;
        btnLocation.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengambil lokasi...';
        setStatus('Mohon tunggu, sedang membaca lokasi Anda. Pastikan GPS aktif.');

        navigator.geolocation.getCurrentPosition(onSuccess, onError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0,
        });
    });

    function onSuccess(pos) {
        const { latitude, longitude, accuracy } = pos.coords;

        document.getElementById('latitude').value = latitude;
        document.getElementById('longitude').value = longitude;
        document.getElementById('locationAccuracy').value = Math.round(accuracy);

        setStatus(
            '<b>Lokasi berhasil dikirim.</b><br>' +
            'Koordinat: ' + latitude.toFixed(5) + ', ' + longitude.toFixed(5) +
            ' <span class="text-muted">(akurasi ±' + Math.round(accuracy) + ' m)</span>',
            'border-success text-success'
        );

        btnLocation.classList.remove('btn-outline-primary');
        btnLocation.classList.add('btn-success');
        btnLocation.innerHTML = '<i class="bi bi-check-lg me-1"></i>Lokasi Terkirim';
        btnStart.disabled = false;
    }

    function onError(err) {
        const messages = {
            1: 'Anda menolak izin akses lokasi. Ujian tidak dapat dimulai tanpa lokasi. Buka pengaturan izin browser, aktifkan akses lokasi untuk situs ini, lalu coba lagi.',
            2: 'Lokasi tidak dapat ditentukan. Pastikan GPS/layanan lokasi perangkat Anda menyala, lalu coba lagi.',
            3: 'Waktu pengambilan lokasi habis. Pindah ke tempat dengan sinyal GPS lebih baik, lalu coba lagi.',
        };

        setStatus(messages[err.code] || 'Gagal mengambil lokasi. Silakan coba lagi.', 'border-danger text-danger');

        btnLocation.disabled = false;
        btnLocation.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Coba Lagi';
        btnStart.disabled = true;
    }
})();
</script>
@endpush
@endif
