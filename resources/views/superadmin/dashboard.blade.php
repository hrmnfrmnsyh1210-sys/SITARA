@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<x-welcome-banner mascot="maskot3" :title="'Halo, ' . auth()->user()->name . '!'" subtitle="Pantau seluruh sekolah dan aktivitas sistem SITARA dari sini." />

<div class="row g-3 mb-4">
    <div class="col-6 col-lg"><x-stat-card icon="bi-building" color="soft-primary" :value="$stats['schools']" label="Sekolah" :delay="0"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-person-badge" color="soft-info" :value="$stats['admins']" label="Admin" :delay="50"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-person-workspace" color="soft-success" :value="$stats['teachers']" label="Guru" :delay="100"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-people" color="soft-warning" :value="$stats['students']" label="Siswa" :delay="150"/></div>
    <div class="col-12 col-lg"><x-stat-card icon="bi-pencil-square" color="soft-purple" :value="$stats['exams']" label="Total Ujian" :delay="200"/></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100" data-aos="fade-up">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-graph-up me-2"></i>Statistik Login (7 Hari Terakhir)</h6>
                <canvas id="loginChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-building me-2"></i>Sekolah Terbaru</h6>
                    <a href="{{ route('superadmin.schools.index') }}" class="small text-decoration-none">Lihat semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($schools as $s)
                    <div class="list-group-item border-0 px-0 d-flex justify-content-between align-items-center">
                        <div><div class="fw-semibold">{{ $s->name }}</div><small class="text-muted">{{ $s->teachers_count }} guru · {{ $s->students_count }} siswa</small></div>
                        <span class="badge bg-soft-{{ $s->is_active ? 'success' : 'danger' }}">{{ $s->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4 mb-0">Belum ada sekolah.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('loginChart'), {
    type: 'line',
    data: {
        labels: @json($loginChart->pluck('label')),
        datasets: [{ label: 'Login', data: @json($loginChart->pluck('count')), borderColor: '#2563EB', backgroundColor: 'rgba(37,99,235,.1)', fill: true, tension: .4 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
