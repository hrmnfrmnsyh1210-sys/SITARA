@extends('layouts.app')
@section('title', 'Periksa Hasil')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('guru.results.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">Periksa: {{ $result->student->name ?? '' }}</h1>
</div>

<div class="card mb-4" data-aos="fade-up"><div class="card-body">
    <div class="row text-center">
        <div class="col"><div class="text-muted small">Nilai</div><div class="fs-3 fw-bold text-primary">{{ $result->total_score }}</div></div>
        <div class="col"><div class="text-muted small">Benar</div><div class="fs-3 fw-bold text-success">{{ $result->correct_count }}</div></div>
        <div class="col"><div class="text-muted small">Salah</div><div class="fs-3 fw-bold text-danger">{{ $result->wrong_count }}</div></div>
        <div class="col"><div class="text-muted small">Status</div><div class="fs-5 fw-bold">{{ $result->is_passed?'Lulus':'Tidak Lulus' }}</div></div>
        <div class="col">
            <div class="text-muted small">Pelanggaran</div>
            <div class="fs-3 fw-bold {{ ($result->violation_count ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">{{ $result->violation_count ?? 0 }}×</div>
        </div>
    </div>
    @if(($result->violation_count ?? 0) > 0)
        <div class="alert alert-warning mt-3 mb-0 small"><i class="bi bi-exclamation-triangle me-1"></i>Siswa terdeteksi <b>keluar dari halaman ujian {{ $result->violation_count }} kali</b> (indikasi kemungkinan mencontek).</div>
    @endif
</div></div>

<form method="POST" action="{{ route('guru.results.grade',$result) }}">
    @csrf
    <div class="card" data-aos="fade-up"><div class="card-body p-4">
        <h6 class="fw-bold mb-3">Jawaban Siswa</h6>
        @foreach($result->answers as $i => $a)
            @php $q = $a->question; $needsManual = $q && !$q->isAutoGradable(); @endphp
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between mb-1">
                    <span class="fw-semibold">Soal {{ $i+1 }} <span class="badge bg-light text-dark ms-1">{{ $q?->type_label }}</span></span>
                    @if(!$needsManual)
                        @if($a->is_correct)<span class="badge bg-soft-success">Benar (+{{ $a->score }})</span>@else<span class="badge bg-soft-danger">Salah</span>@endif
                    @endif
                </div>
                <div class="text-muted small mb-2">{{ Str::limit($q->question_text ?? '', 160) }}</div>
                <div class="bg-light rounded p-2 mb-2"><small class="text-muted">Jawaban siswa:</small><br>{{ is_array($a->answer) ? implode(', ', $a->answer) : '—' }}</div>
                @if($needsManual)
                    <div class="row g-2 align-items-center">
                        <div class="col-auto"><label class="form-label mb-0 small">Beri nilai (maks {{ $q->score }}):</label></div>
                        <div class="col-3"><input type="number" step="0.5" min="0" max="{{ $q->score }}" name="scores[{{ $a->id }}]" value="{{ $a->score }}" class="form-control form-control-sm"></div>
                    </div>
                @endif
            </div>
        @endforeach
        <button class="btn btn-primary mt-2"><i class="bi bi-check-lg me-1"></i>Simpan Nilai & Finalisasi</button>
    </div></div>
</form>
@endsection
