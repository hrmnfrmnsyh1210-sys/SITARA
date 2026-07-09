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

    @if($result->hasLocation())
        <div class="alert alert-light mt-3 mb-0 small d-flex justify-content-between align-items-center gap-2">
            <span>
                <i class="bi bi-geo-alt-fill me-1 text-primary"></i>
                Lokasi saat memulai: <b>{{ $result->latitude }}, {{ $result->longitude }}</b>
                @if($result->location_accuracy)
                    <span class="text-muted">(akurasi ±{{ $result->location_accuracy }} m)</span>
                @endif
                @if($result->location_captured_at)
                    <span class="text-muted">— {{ $result->location_captured_at->format('d/m/Y H:i') }}</span>
                @endif
            </span>
            <a href="{{ $result->mapsUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary flex-shrink-0">
                <i class="bi bi-map me-1"></i>Lihat Peta
            </a>
        </div>
    @elseif($result->examSchedule?->requires_location)
        <div class="alert alert-secondary mt-3 mb-0 small"><i class="bi bi-geo-alt me-1"></i>Lokasi tidak terekam (ujian dimulai sebelum syarat lokasi diaktifkan).</div>
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

        @if(($result->violation_count ?? 0) > 0)
            @php $ov = is_null($result->pass_override) ? 'auto' : ($result->pass_override ? 'pass' : 'fail'); @endphp
            <div class="border rounded p-3 mb-2 bg-soft-danger-subtle" style="background:#fff5f5">
                <div class="fw-semibold mb-1"><i class="bi bi-shield-exclamation text-danger me-1"></i>Keputusan Kelulusan</div>
                <p class="text-muted small mb-2">Siswa ini punya <b>{{ $result->violation_count }} pelanggaran</b>. Kamu bisa menentukan kelulusannya secara manual, atau biarkan <b>Otomatis</b> (mengikuti nilai).</p>
                <div class="row g-2 align-items-center">
                    <div class="col-auto"><label class="form-label mb-0 small" for="pass_override">Status kelulusan:</label></div>
                    <div class="col-auto">
                        <select name="pass_override" id="pass_override" class="form-select form-select-sm">
                            <option value="auto" @selected($ov==='auto')>Otomatis (ikut nilai)</option>
                            <option value="pass" @selected($ov==='pass')>Luluskan</option>
                            <option value="fail" @selected($ov==='fail')>Tidak luluskan</option>
                        </select>
                    </div>
                    @unless(is_null($result->pass_override))
                        <div class="col-auto"><span class="badge bg-soft-info"><i class="bi bi-hand-index me-1"></i>Keputusan manual guru</span></div>
                    @endunless
                </div>
            </div>
        @endif

        <button class="btn btn-primary mt-2"><i class="bi bi-check-lg me-1"></i>Simpan Nilai & Finalisasi</button>
    </div></div>
</form>
@endsection
