@extends('layouts.app')
@section('title', $announcement->exists ? 'Edit Pengumuman' : 'Buat Pengumuman')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.announcements.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $announcement->exists ? 'Edit' : 'Buat' }} Pengumuman</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $announcement->exists ? route('admin.announcements.update',$announcement) : route('admin.announcements.store') }}">
        @csrf @if($announcement->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Judul <span class="text-danger">*</span></label><input name="title" value="{{ old('title',$announcement->title) }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Target</label><select name="target" class="form-select"><option value="all" @selected(old('target',$announcement->target)==='all')>Semua</option><option value="teachers" @selected(old('target',$announcement->target)==='teachers')>Guru</option><option value="students" @selected(old('target',$announcement->target)==='students')>Siswa</option></select></div>
            <div class="col-12"><label class="form-label">Isi <span class="text-danger">*</span></label><textarea name="content" rows="5" class="form-control" required>{{ old('content',$announcement->content) }}</textarea></div>
            <div class="col-12"><div class="form-check form-switch"><input type="checkbox" name="is_published" value="1" class="form-check-input" id="pub" @checked(old('is_published',$announcement->is_published ?? true))><label class="form-check-label" for="pub">Publikasikan</label></div></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
