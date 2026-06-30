@extends('layouts.app')
@section('title', $room->exists ? 'Edit Ruangan' : 'Tambah Ruangan')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('admin.rooms.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $room->exists ? 'Edit' : 'Tambah' }} Ruangan</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $room->exists ? route('admin.rooms.update',$room) : route('admin.rooms.store') }}">
        @csrf @if($room->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nama <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$room->name) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Kapasitas <span class="text-danger">*</span></label><input type="number" name="capacity" value="{{ old('capacity',$room->capacity ?? 40) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Lokasi</label><input name="location" value="{{ old('location',$room->location) }}" class="form-control"></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
