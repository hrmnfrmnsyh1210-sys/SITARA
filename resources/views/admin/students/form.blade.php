@extends('layouts.app')
@section('title', $student->exists ? 'Edit Siswa' : 'Tambah Siswa')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.students.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $student->exists ? 'Edit' : 'Tambah' }} Siswa</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $student->exists ? route('admin.students.update',$student) : route('admin.students.store') }}">
        @csrf @if($student->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$student->name) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">NIS <span class="text-danger">*</span></label><input name="nis" value="{{ old('nis',$student->nis) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">NISN</label><input name="nisn" value="{{ old('nisn',$student->nisn) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Kelas</label><select name="classroom_id" class="form-select"><option value="">— Pilih —</option>@foreach($classrooms as $c)<option value="{{ $c->id }}" @selected(old('classroom_id',$student->classroom_id)==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label">JK</label><select name="gender" class="form-select"><option value="">—</option><option value="L" @selected(old('gender',$student->gender)==='L')>L</option><option value="P" @selected(old('gender',$student->gender)==='P')>P</option></select></div>
            <div class="col-md-3"><label class="form-label">Tempat Lahir</label><input name="birth_place" value="{{ old('birth_place',$student->birth_place) }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label">Tanggal Lahir</label><input type="date" name="birth_date" value="{{ old('birth_date',$student->birth_date?->format('Y-m-d')) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Telepon</label><input name="phone" value="{{ old('phone',$student->phone) }}" class="form-control"></div>
            <div class="col-md-8"><label class="form-label">Alamat</label><input name="address" value="{{ old('address',$student->address) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Kata Sandi {{ $student->exists ? '(kosongkan jika tetap)' : '' }}</label><input type="password" name="password" class="form-control" {{ $student->exists ? '' : 'required' }}></div>
        </div>
        <p class="text-muted small mt-2"><i class="bi bi-info-circle me-1"></i>Siswa login menggunakan <strong>NIS</strong> dan kata sandi di atas.</p>
        <div class="mt-3"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
