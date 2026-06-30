@extends('layouts.app')
@section('title', $bank->exists ? 'Edit Bank Soal' : 'Buat Bank Soal')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('guru.question-banks.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $bank->exists ? 'Edit' : 'Buat' }} Bank Soal</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $bank->exists ? route('guru.question-banks.update',$bank) : route('guru.question-banks.store') }}">
        @csrf @if($bank->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Nama Bank Soal <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$bank->name) }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label><select name="subject_id" class="form-select" required><option value="">— Pilih —</option>@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(old('subject_id',$bank->subject_id)==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
            <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="description" rows="2" class="form-control">{{ old('description',$bank->description) }}</textarea></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
