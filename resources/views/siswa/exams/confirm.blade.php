@extends('layouts.app')
@section('title', 'Konfirmasi Ujian')

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

                <form method="POST" action="{{ route('siswa.exams.start',$schedule) }}">
                    @csrf
                    <a href="{{ route('siswa.exams.index') }}" class="btn btn-light px-4">Batal</a>
                    <button class="btn btn-primary px-4">{{ $result ? 'Lanjutkan Ujian' : 'Mulai Ujian' }} <i class="bi bi-arrow-right ms-1"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
