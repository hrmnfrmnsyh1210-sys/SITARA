@extends('layouts.app')
@section('title', $schedule->exists ? 'Edit Jadwal' : 'Buat Jadwal')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('guru.schedules.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $schedule->exists ? 'Edit' : 'Buat' }} Jadwal Ujian</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $schedule->exists ? route('guru.schedules.update',$schedule) : route('guru.schedules.store') }}">
        @csrf @if($schedule->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Ujian <span class="text-danger">*</span></label><select name="exam_id" class="form-select" required><option value="">— Pilih —</option>@foreach($exams as $e)<option value="{{ $e->id }}" @selected(old('exam_id',$schedule->exam_id ?? $selectedExam)==$e->id)>{{ $e->title }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Kelas <span class="text-danger">*</span></label><select name="classroom_id" class="form-select" required><option value="">— Pilih —</option>@foreach($classrooms as $c)<option value="{{ $c->id }}" @selected(old('classroom_id',$schedule->classroom_id)==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Ruang Ujian</label><select name="room_id" class="form-select"><option value="">— Tanpa ruang —</option>@foreach($rooms as $r)<option value="{{ $r->id }}" @selected(old('room_id',$schedule->room_id)==$r->id)>{{ $r->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Waktu Mulai <span class="text-danger">*</span></label><input type="datetime-local" name="start_time" value="{{ old('start_time',$schedule->start_time?->format('Y-m-d\TH:i')) }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Waktu Selesai <span class="text-danger">*</span></label><input type="datetime-local" name="end_time" value="{{ old('end_time',$schedule->end_time?->format('Y-m-d\TH:i')) }}" class="form-control" required></div>
            <div class="col-12">
                <div class="form-check form-switch">
                    <input type="checkbox" name="requires_location" value="1" class="form-check-input" id="reqloc" @checked(old('requires_location', $schedule->requires_location))>
                    <label class="form-check-label" for="reqloc">Wajib kirim lokasi</label>
                </div>
                <div class="form-text"><i class="bi bi-geo-alt me-1"></i>Siswa harus mengizinkan akses lokasi dan mengirim koordinatnya sebelum bisa memulai ujian. Koordinat disimpan sebagai bukti, tidak dipakai untuk memblokir berdasarkan jarak.</div>
            </div>
            @if($schedule->exists)
            <div class="col-12"><div class="form-check form-switch"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="act" @checked($schedule->is_active)><label class="form-check-label" for="act">Jadwal aktif</label></div></div>
            @endif
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
