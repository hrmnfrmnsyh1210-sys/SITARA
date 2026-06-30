@extends('layouts.app')
@section('title', $year->exists ? 'Edit Tahun Ajaran' : 'Tambah Tahun Ajaran')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.academic-years.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $year->exists ? 'Edit' : 'Tambah' }} Tahun Ajaran</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $year->exists ? route('admin.academic-years.update',$year) : route('admin.academic-years.store') }}">
        @csrf @if($year->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$year->name) }}" class="form-control" placeholder="2024/2025" required></div>
            <div class="col-md-4"><label class="form-label">Tanggal Mulai</label><input type="date" name="start_date" value="{{ old('start_date',$year->start_date?->format('Y-m-d')) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Tanggal Selesai</label><input type="date" name="end_date" value="{{ old('end_date',$year->end_date?->format('Y-m-d')) }}" class="form-control"></div>
            <div class="col-12"><div class="form-check form-switch"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="act" @checked(old('is_active',$year->is_active))><label class="form-check-label" for="act">Jadikan tahun ajaran aktif</label></div></div>
        </div>
        @unless($year->exists)<p class="text-muted small mt-2"><i class="bi bi-info-circle me-1"></i>Semester Ganjil & Genap akan dibuat otomatis.</p>@endunless
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
