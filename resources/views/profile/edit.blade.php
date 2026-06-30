@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="row g-4">
    <div class="col-lg-4" data-aos="fade-up">
        <div class="card text-center">
            <div class="card-body p-4">
                <img src="{{ $user->avatar_url }}" class="rounded-circle mb-3" style="width:110px;height:110px;object-fit:cover">
                <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                <span class="badge bg-soft-primary mt-2">{{ ucfirst(str_replace('_',' ',$user->role)) }}</span>
                <p class="text-muted small mt-2 mb-0">{{ $user->email ?? $user->username }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card mb-4" data-aos="fade-up">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-person me-2"></i>Informasi Profil</h6>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3"><label class="form-label">Nama</label><input name="name" value="{{ old('name',$user->name) }}" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input name="email" value="{{ old('email',$user->email) }}" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">No. Telepon</label><input name="phone" value="{{ old('phone',$user->phone) }}" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Foto Profil</label><input type="file" name="avatar" class="form-control" accept="image/*"></div>
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                </form>
            </div>
        </div>
        <div class="card" data-aos="fade-up">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-lock me-2"></i>Ubah Kata Sandi</h6>
                <form method="POST" action="{{ route('profile.password') }}">
                    @csrf @method('PUT')
                    <div class="mb-3"><label class="form-label">Kata Sandi Saat Ini</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Kata Sandi Baru</label><input type="password" name="password" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Konfirmasi</label><input type="password" name="password_confirmation" class="form-control" required></div>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-shield-check me-1"></i>Ubah Kata Sandi</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
