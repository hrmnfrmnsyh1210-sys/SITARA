@extends('layouts.app')
@section('title', 'Jadwal Ujian')

@section('content')
<h1 class="page-title mb-3">Jadwal Ujian Saya</h1>
<div class="row g-3">
    @forelse($schedules as $s)
        @php $status = $resultsBySchedule[$s->id] ?? null; @endphp
        <div class="col-md-6 col-xl-4" data-aos="fade-up">
            <div class="card h-100"><div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="badge bg-soft-{{ $s->isOngoing()?'success':($s->isUpcoming()?'info':'secondary') }}">{{ $s->statusLabel() }}</span>
                    @if(in_array($status,['submitted','graded']))<span class="badge bg-soft-success"><i class="bi bi-check2 me-1"></i>Selesai</span>@endif
                </div>
                <h6 class="fw-bold mb-1">{{ $s->exam->title }}</h6>
                <p class="text-muted small mb-2">{{ $s->exam->subject->name ?? '' }}</p>
                <ul class="list-unstyled small text-muted mb-3">
                    <li><i class="bi bi-clock me-1"></i>{{ $s->start_time->format('d M Y H:i') }} — {{ $s->end_time->format('H:i') }}</li>
                    <li><i class="bi bi-hourglass-split me-1"></i>{{ $s->exam->duration_minutes }} menit</li>
                    <li><i class="bi bi-building me-1"></i>{{ $s->room->name ?? 'Online' }}</li>
                </ul>
                @if(in_array($status,['submitted','graded']))
                    <a href="{{ route('siswa.results.index') }}" class="btn btn-light w-100">Lihat Hasil</a>
                @elseif($s->isOngoing())
                    <a href="{{ route('siswa.exams.confirm',$s) }}" class="btn btn-primary w-100"><i class="bi bi-play-fill me-1"></i>Kerjakan Sekarang</a>
                @elseif($s->isUpcoming())
                    <button class="btn btn-light w-100" disabled>Belum Dibuka</button>
                @else
                    <button class="btn btn-light w-100" disabled>Ditutup</button>
                @endif
            </div></div>
        </div>
    @empty
        <div class="col-12"><div class="card"><div class="card-body text-center text-muted py-5"><i class="bi bi-calendar-x fs-1 d-block mb-2"></i>Tidak ada jadwal ujian.</div></div></div>
    @endforelse
</div>
@endsection
