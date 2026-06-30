@extends('layouts.app')
@section('title', $admin->exists ? 'Edit Admin' : 'Tambah Admin')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('superadmin.admins.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $admin->exists ? 'Edit' : 'Tambah' }} Admin Sekolah</h1>
</div>

@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $admin->exists ? route('superadmin.admins.update',$admin) : route('superadmin.admins.store') }}">
        @csrf @if($admin->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Sekolah <span class="text-danger">*</span></label>
                <select name="school_id" class="form-select" required><option value="">— Pilih —</option>@foreach($schools as $s)<option value="{{ $s->id }}" @selected(old('school_id',$admin->school_id)==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$admin->name) }}" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input name="email" value="{{ old('email',$admin->email) }}" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Telepon</label><input name="phone" value="{{ old('phone',$admin->phone) }}" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Kata Sandi {{ $admin->exists ? '(kosongkan jika tidak diubah)' : '' }}</label><input type="password" name="password" class="form-control" {{ $admin->exists ? '' : 'required' }}></div>
            <div class="col-md-6"><label class="form-label">Konfirmasi Kata Sandi</label><input type="password" name="password_confirmation" class="form-control"></div>
            @if($admin->exists)
            <div class="col-12"><div class="form-check form-switch"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="act" @checked($admin->is_active)><label class="form-check-label" for="act">Akun Aktif</label></div></div>
            @endif
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
