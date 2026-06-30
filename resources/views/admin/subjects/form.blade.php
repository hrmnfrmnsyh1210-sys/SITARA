@extends('layouts.app')
@section('title', $subject->exists ? 'Edit Mapel' : 'Tambah Mapel')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.subjects.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $subject->exists ? 'Edit' : 'Tambah' }} Mata Pelajaran</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $subject->exists ? route('admin.subjects.update',$subject) : route('admin.subjects.store') }}">
        @csrf @if($subject->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Kode</label><input name="code" value="{{ old('code',$subject->code) }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$subject->name) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Warna</label><input type="color" name="color" value="{{ old('color',$subject->color ?? '#2563EB') }}" class="form-control form-control-color w-100"></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
