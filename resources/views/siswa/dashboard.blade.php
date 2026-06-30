@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<x-welcome-banner mascot="maskot1" :title="'Halo, ' . auth()->user()->name . '!'" subtitle="Semangat belajar! Cek jadwal ujian dan nilai terbarumu di sini." />

@unless($student)
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Akun Anda belum terhubung ke data siswa. Hubungi admin sekolah.</div>
@endunless

<div class="row g-3 mb-4">
    <div class="col-md-4"><x-stat-card icon="bi-calendar-event" color="soft-primary" :value="$stats['upcoming']" label="Ujian Mendatang" :delay="0"/></div>
    <div class="col-md-4"><x-stat-card icon="bi-check2-circle" color="soft-success" :value="$stats['completed']" label="Ujian Selesai" :delay="50"/></div>
    <div class="col-md-4"><x-stat-card icon="bi-award" color="soft-warning" :value="$stats['avg']" label="Rata-rata Nilai" :delay="100"/></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card mb-4" data-aos="fade-up">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-calendar-week me-2"></i>Jadwal Ujian</h6>
                @forelse($upcoming as $s)
                    <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2">
                        <div>
                            <div class="fw-semibold">{{ $s->exam->title }}</div>
                            <small class="text-muted">{{ $s->exam->subject->name ?? '' }} · {{ $s->start_time->format('d M Y H:i') }}</small>
                        </div>
                        @if($s->isOngoing())
                            <a href="{{ route('siswa.exams.confirm', $s) }}" class="btn btn-primary btn-sm">Kerjakan</a>
                        @else
                            <span class="badge bg-soft-info">{{ $s->statusLabel() }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-muted text-center py-4 mb-0">Tidak ada jadwal ujian.</p>
                @endforelse
            </div>
        </div>
        <div class="card" data-aos="fade-up">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-award me-2"></i>Nilai Terbaru</h6>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Ujian</th><th>Status</th><th class="text-end">Nilai</th></tr></thead>
                        <tbody>
                        @forelse($results as $r)
                            <tr>
                                <td>{{ $r->examSchedule->exam->title ?? '-' }}</td>
                                <td><span class="badge bg-soft-{{ $r->status==='graded'?'success':'secondary' }}">{{ $r->status==='graded'?'Dinilai':'Menunggu' }}</span></td>
                                <td class="text-end fw-bold">{{ $r->status==='graded' && $r->examSchedule->exam->show_result ? $r->total_score : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada nilai.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-megaphone me-2"></i>Pengumuman</h6>
                @forelse($announcements as $a)
                    <div class="border-start border-3 border-primary ps-3 mb-3">
                        <div class="fw-semibold">{{ $a->title }}</div>
                        <p class="text-muted small mb-1">{{ Str::limit($a->content, 120) }}</p>
                        <small class="text-muted">{{ $a->created_at->diffForHumans() }}</small>
                    </div>
                @empty
                    <p class="text-muted text-center py-4 mb-0">Tidak ada pengumuman.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
