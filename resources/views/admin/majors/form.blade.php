@extends('layouts.app')
@section('title', $major->exists ? 'Edit Jurusan' : 'Tambah Jurusan')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.majors.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $major->exists ? 'Edit' : 'Tambah' }} Jurusan</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $major->exists ? route('admin.majors.update',$major) : route('admin.majors.store') }}">
        @csrf @if($major->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Kode</label><input name="code" value="{{ old('code',$major->code) }}" class="form-control"></div>
            <div class="col-md-9"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$major->name) }}" class="form-control" required></div>
            <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="description" rows="2" class="form-control">{{ old('description',$major->description) }}</textarea></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
