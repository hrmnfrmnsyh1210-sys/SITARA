@extends('layouts.app')
@section('title', 'Import Data Siswa')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.students.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">Import Data Siswa</h1>
</div>

@if(session('import_errors') && count(session('import_errors')))
    <div class="alert alert-warning" data-aos="fade-up">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Beberapa baris dilewati:</div>
        <ul class="mb-0 small">
            @foreach(session('import_errors') as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card" data-aos="fade-up"><div class="card-body p-4">
            <h5 class="mb-3"><i class="bi bi-upload me-1"></i>Unggah Berkas Excel</h5>
            <form method="POST" action="{{ route('admin.students.import.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Berkas <span class="text-danger">*</span> <span class="text-muted small">(.xlsx / .xls, maks 5MB)</span></label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls" required>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mulai Import</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-5">
        <div class="card" data-aos="fade-up"><div class="card-body p-4">
            <h5 class="mb-3"><i class="bi bi-file-earmark-arrow-down me-1"></i>Template</h5>
            <p class="text-muted small">Unduh template, isi data siswa sesuai kolom, lalu unggah kembali.</p>
            <a href="{{ route('admin.students.import.template') }}" class="btn btn-outline-primary w-100 mb-3"><i class="bi bi-download me-1"></i>Unduh Template Excel</a>

            <div class="border-top pt-3">
                <h6 class="small fw-bold text-uppercase text-muted">Kolom yang dikenali</h6>
                <ul class="small mb-3">
                    <li><strong>nis</strong>, <strong>nama</strong>, <strong>tanggal_lahir</strong> — wajib</li>
                    <li>nisn, jenis_kelamin (L/P), tempat_lahir, no_hp, alamat, kelas — opsional</li>
                </ul>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-shield-lock me-1"></i>
                    Password default tiap siswa adalah <strong>tanggal lahir</strong> dengan format
                    <code>ddmmyyyy</code> (contoh: 17 Mei 2008 → <code>17052008</code>).
                    Siswa <strong>wajib mengganti password</strong> saat login pertama.
                </div>
                <p class="small text-muted mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Nama <strong>kelas</strong> harus sama persis dengan kelas yang sudah terdaftar. NIS yang sudah ada akan dilewati.</p>
            </div>
        </div></div>
    </div>
</div>
@endsection
