@extends('layouts.app')
@section('title', $school->exists ? 'Edit Sekolah' : 'Tambah Sekolah')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('superadmin.schools.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $school->exists ? 'Edit' : 'Tambah' }} Sekolah</h1>
</div>

@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card" data-aos="fade-up">
    <div class="card-body p-4">
        <form method="POST" action="{{ $school->exists ? route('superadmin.schools.update',$school) : route('superadmin.schools.store') }}" enctype="multipart/form-data">
            @csrf
            @if($school->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">Nama Sekolah <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$school->name) }}" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Jenjang</label>
                    <select name="level" class="form-select">
                        @foreach(['','SD','SMP','SMA','SMK'] as $lv)<option value="{{ $lv }}" @selected(old('level',$school->level)===$lv)>{{ $lv ?: '— Pilih —' }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4"><label class="form-label">NPSN</label><input name="npsn" value="{{ old('npsn',$school->npsn) }}" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Telepon</label><input name="phone" value="{{ old('phone',$school->phone) }}" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Email</label><input name="email" value="{{ old('email',$school->email) }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">Kepala Sekolah</label><input name="principal_name" value="{{ old('principal_name',$school->principal_name) }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Website</label><input name="website" value="{{ old('website',$school->website) }}" class="form-control"></div>
                <div class="col-md-3"><label class="form-label">Warna Tema</label><input type="color" name="primary_color" value="{{ old('primary_color',$school->primary_color ?? '#2563EB') }}" class="form-control form-control-color w-100"></div>
                <div class="col-12"><label class="form-label">Alamat</label><textarea name="address" rows="2" class="form-control">{{ old('address',$school->address) }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="logo" class="form-control" accept="image/*">@if($school->logo)<img src="{{ $school->logo_url }}" class="mt-2 rounded" style="height:48px">@endif</div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check form-switch"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="active" @checked(old('is_active',$school->is_active ?? true))><label class="form-check-label" for="active">Sekolah Aktif</label></div>
                </div>
            </div>
            <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
        </form>
    </div>
</div>
@endsection
