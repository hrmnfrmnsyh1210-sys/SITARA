@extends('layouts.app')
@section('title', 'Import Soal · ' . $questionBank->name)

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('guru.question-banks.questions.index', $questionBank) }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h1 class="page-title">Import Soal dari Word</h1>
        <small class="text-muted">{{ $questionBank->name }}</small>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card" data-aos="fade-up"><div class="card-body p-4">
            <h5 class="mb-3"><i class="bi bi-file-earmark-word me-1"></i>Unggah Dokumen Word</h5>
            <form method="POST" action="{{ route('guru.question-banks.questions.import.store', $questionBank) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Berkas <span class="text-danger">*</span> <span class="text-muted small">(.docx, maks 5MB)</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".docx" required>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mulai Import</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-5">
        <div class="card" data-aos="fade-up"><div class="card-body p-4">
            <h5 class="mb-3"><i class="bi bi-file-earmark-arrow-down me-1"></i>Template &amp; Format</h5>
            <a href="{{ route('guru.questions.import.template') }}" class="btn btn-outline-primary w-100 mb-3"><i class="bi bi-download me-1"></i>Unduh Template Word</a>

            <p class="small text-muted mb-2">Pisahkan tiap soal dengan <strong>satu baris kosong</strong>. Tag tipe opsional:</p>
            <ul class="small mb-3">
                <li><code>[PG]</code> pilihan ganda + baris <code>JAWABAN: A</code></li>
                <li><code>[BS]</code> benar/salah → <code>JAWABAN: Benar</code></li>
                <li><code>[ISIAN]</code> isian singkat → <code>JAWABAN: 20 | dua puluh</code></li>
                <li><code>[JODOH]</code> menjodohkan → baris <code>kiri = kanan</code></li>
                <li><code>[ESSAY]</code> / <code>[UPLOAD]</code> tanpa jawaban</li>
            </ul>
            <div class="alert alert-info small mb-0">
                <i class="bi bi-info-circle me-1"></i>Baris <code>SKOR:</code>, <code>TINGKAT:</code> (mudah/sedang/sulit), dan <code>PEMBAHASAN:</code> bersifat opsional.
            </div>
        </div></div>
    </div>
</div>
@endsection
