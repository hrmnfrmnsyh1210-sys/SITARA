@extends('layouts.app')
@section('title', $teacher->exists ? 'Edit Guru' : 'Tambah Guru')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $teacher->exists ? 'Edit' : 'Tambah' }} Guru</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $teacher->exists ? route('admin.teachers.update',$teacher) : route('admin.teachers.store') }}" enctype="multipart/form-data">
        @csrf @if($teacher->exists) @method('PUT') @endif
        <h6 class="fw-bold text-muted mb-3">Data Pribadi</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$teacher->name) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">NIP</label><input name="nip" value="{{ old('nip',$teacher->nip) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Jenis Kelamin</label><select name="gender" class="form-select"><option value="">—</option><option value="L" @selected(old('gender',$teacher->gender)==='L')>Laki-laki</option><option value="P" @selected(old('gender',$teacher->gender)==='P')>Perempuan</option></select></div>
            <div class="col-md-4"><label class="form-label">Telepon</label><input name="phone" value="{{ old('phone',$teacher->phone) }}" class="form-control"></div>
            <div class="col-md-8"><label class="form-label">Alamat</label><input name="address" value="{{ old('address',$teacher->address) }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Foto</label><input type="file" name="photo" class="form-control" accept="image/*"></div>
        </div>
        <h6 class="fw-bold text-muted mb-3">Akun Login</h6>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input name="email" value="{{ old('email',$teacher->user->email ?? '') }}" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Kata Sandi {{ $teacher->exists ? '(kosongkan jika tetap)' : '' }}</label><input type="password" name="password" class="form-control" {{ $teacher->exists ? '' : 'required' }}></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
