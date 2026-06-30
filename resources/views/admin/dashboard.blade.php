@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<x-welcome-banner mascot="maskot4" :title="'Halo, ' . auth()->user()->name . '!'" :subtitle="'Selamat datang di panel admin ' . (auth()->user()->school->name ?? 'sekolah') . '.'" />

<div class="row g-3 mb-4">
    <div class="col-6 col-lg"><x-stat-card icon="bi-person-workspace" color="soft-primary" :value="$stats['teachers']" label="Guru" :delay="0"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-people" color="soft-success" :value="$stats['students']" label="Siswa" :delay="50"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-door-open" color="soft-info" :value="$stats['classrooms']" label="Kelas" :delay="100"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-collection" color="soft-purple" :value="$stats['questions']" label="Soal" :delay="150"/></div>
    <div class="col-12 col-lg"><x-stat-card icon="bi-pencil-square" color="soft-warning" :value="$stats['exams']" label="Ujian" :delay="200"/></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100" data-aos="fade-up">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2"></i>Rata-rata Nilai per Mapel</h6>
                @if($scoreChart->isEmpty())
                    <p class="text-muted text-center py-5 mb-0">Belum ada data nilai.</p>
                @else
                    <canvas id="scoreChart" height="130"></canvas>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100" data-aos="fade-up" data-aos-delay="100">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="bi bi-calendar-event me-2"></i>Ujian Hari Ini</h6>
                <div class="list-group list-group-flush">
                    @forelse($examsToday as $sch)
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex justify-content-between">
                            <div class="fw-semibold">{{ $sch->exam->title }}</div>
                            <small class="text-muted">{{ $sch->start_time->format('H:i') }}</small>
                        </div>
                        <small class="text-muted">{{ $sch->exam->subject->name ?? '' }} · {{ $sch->classroom->name ?? '' }}</small>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4 mb-0">Tidak ada ujian hari ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($scoreChart->isNotEmpty())
<script>
new Chart(document.getElementById('scoreChart'), {
    type: 'bar',
    data: {
        labels: @json($scoreChart->keys()),
        datasets: [{ label: 'Rata-rata', data: @json($scoreChart->values()), backgroundColor: '#2563EB', borderRadius: 8 }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } }
});
</script>
@endif
@endpush
