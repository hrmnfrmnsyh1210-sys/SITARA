@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<x-welcome-banner mascot="maskot2" :title="'Halo, ' . auth()->user()->name . '!'" subtitle="Kelola bank soal, paket ujian, dan nilai siswa Anda dengan mudah." />

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><x-stat-card icon="bi-collection" color="soft-primary" :value="$stats['questions']" label="Soal" :delay="0"/></div>
    <div class="col-6 col-lg-3"><x-stat-card icon="bi-box-seam" color="soft-success" :value="$stats['packages']" label="Paket Soal" :delay="50"/></div>
    <div class="col-6 col-lg-3"><x-stat-card icon="bi-pencil-square" color="soft-info" :value="$stats['exams']" label="Ujian" :delay="100"/></div>
    <div class="col-6 col-lg-3"><x-stat-card icon="bi-calendar-week" color="soft-warning" :value="$stats['schedules']" label="Jadwal" :delay="150"/></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card" data-aos="fade-up">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-calendar-week me-2"></i>Jadwal Ujian Mendatang</h6>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Ujian</th><th>Kelas</th><th>Mulai</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($upcoming as $s)
                            <tr>
                                <td class="fw-semibold">{{ $s->exam->title }}<br><small class="text-muted">{{ $s->exam->subject->name ?? '' }}</small></td>
                                <td>{{ $s->classroom->name ?? '-' }}</td>
                                <td>{{ $s->start_time->format('d M Y H:i') }}</td>
                                <td><span class="badge bg-soft-info">{{ $s->statusLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada jadwal.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-lightning me-2"></i>Aksi Cepat</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('guru.question-banks.index') }}" class="btn btn-outline-primary text-start"><i class="bi bi-collection me-2"></i>Kelola Bank Soal</a>
                    <a href="{{ route('guru.packages.index') }}" class="btn btn-outline-primary text-start"><i class="bi bi-box-seam me-2"></i>Buat Paket Soal</a>
                    <a href="{{ route('guru.exams.create') }}" class="btn btn-outline-primary text-start"><i class="bi bi-plus-circle me-2"></i>Buat Ujian Baru</a>
                    <a href="{{ route('guru.results.index') }}" class="btn btn-outline-primary text-start"><i class="bi bi-clipboard-check me-2"></i>Koreksi Hasil</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
