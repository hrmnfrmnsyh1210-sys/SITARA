@extends('layouts.app')
@section('title', $classroom->exists ? 'Edit Kelas' : 'Tambah Kelas')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.classrooms.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $classroom->exists ? 'Edit' : 'Tambah' }} Kelas</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $classroom->exists ? route('admin.classrooms.update',$classroom) : route('admin.classrooms.store') }}">
        @csrf @if($classroom->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nama Kelas <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$classroom->name) }}" class="form-control" placeholder="X IPA 1" required></div>
            <div class="col-md-6"><label class="form-label">Tingkat</label>
                <select name="grade_level" class="form-select"><option value="">— Pilih —</option>@foreach(['X','XI','XII','7','8','9'] as $g)<option value="{{ $g }}" @selected(old('grade_level',$classroom->grade_level)===$g)>{{ $g }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Jurusan</label>
                <select name="major_id" class="form-select"><option value="">— Tidak ada —</option>@foreach($majors as $m)<option value="{{ $m->id }}" @selected(old('major_id',$classroom->major_id)==$m->id)>{{ $m->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Wali Kelas</label>
                <select name="homeroom_teacher_id" class="form-select"><option value="">— Tidak ada —</option>@foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('homeroom_teacher_id',$classroom->homeroom_teacher_id)==$t->id)>{{ $t->name }}</option>@endforeach</select></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
