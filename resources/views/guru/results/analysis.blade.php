@extends('layouts.app')
@section('title', 'Analisis Ujian')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('guru.results.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">Analisis: {{ $exam->title }}</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg"><x-stat-card icon="bi-people" color="soft-primary" :value="$participants" label="Peserta"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-graph-up" color="soft-info" :value="$avg" label="Rata-rata"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-check-circle" color="soft-success" :value="$passed" label="Lulus"/></div>
    <div class="col-6 col-lg"><x-stat-card icon="bi-arrow-up" color="soft-warning" :value="$highest" label="Tertinggi"/></div>
    <div class="col-12 col-lg"><x-stat-card icon="bi-arrow-down" color="soft-danger" :value="$lowest" label="Terendah"/></div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100" data-aos="fade-up"><div class="card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-trophy me-2"></i>Peringkat Siswa</h6>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>#</th><th>Siswa</th><th class="text-end">Nilai</th></tr></thead>
                <tbody>
                @forelse($ranking as $i => $r)
                    <tr>
                        <td>@if($i<3)<i class="bi bi-trophy-fill text-{{ ['warning','secondary','danger'][$i] }}"></i>@else{{ $i+1 }}@endif</td>
                        <td>{{ $r->student->name ?? '-' }}</td>
                        <td class="text-end fw-bold {{ $r->is_passed?'text-success':'text-danger' }}">{{ $r->total_score }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Belum ada peserta.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100" data-aos="fade-up" data-aos-delay="100"><div class="card-body">
            <h6 class="fw-bold mb-3"><i class="bi bi-clipboard-data me-2"></i>Analisis Butir Soal</h6>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead><tr><th>Soal</th><th class="text-center">% Benar</th><th class="text-center">Tingkat</th></tr></thead>
                <tbody>
                @forelse($itemStats as $i => $stat)
                    <tr>
                        <td><small>{{ $i+1 }}. {{ $stat['text'] }}</small></td>
                        <td class="text-center">
                            <div class="progress" style="height:18px"><div class="progress-bar bg-{{ $stat['correct_pct']>=70?'success':($stat['correct_pct']>=40?'warning':'danger') }}" style="width:{{ $stat['correct_pct'] }}%">{{ $stat['correct_pct'] }}%</div></div>
                        </td>
                        <td class="text-center"><span class="badge bg-soft-{{ $stat['level']==='Mudah'?'success':($stat['level']==='Sedang'?'warning':'danger') }}">{{ $stat['level'] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div></div>
    </div>
</div>
@endsection
