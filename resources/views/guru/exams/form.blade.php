@extends('layouts.app')
@section('title', $exam->exists ? 'Edit Ujian' : 'Buat Ujian')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('guru.exams.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $exam->exists ? 'Edit' : 'Buat' }} Ujian</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

@if(count($packages) === 0)
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-1"></i>Anda belum punya paket soal. <a href="{{ route('guru.packages.create') }}">Buat paket dulu</a>.</div>
@endif

<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <form method="POST" action="{{ $exam->exists ? route('guru.exams.update',$exam) : route('guru.exams.store') }}">
        @csrf @if($exam->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Judul Ujian <span class="text-danger">*</span></label><input name="title" value="{{ old('title',$exam->title) }}" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(['draft'=>'Draft','published'=>'Publikasikan','closed'=>'Tutup'] as $v=>$l)<option value="{{ $v }}" @selected(old('status',$exam->status ?? 'draft')===$v)>{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Paket Soal <span class="text-danger">*</span></label><select name="exam_package_id" class="form-select" required><option value="">— Pilih —</option>@foreach($packages as $p)<option value="{{ $p->id }}" @selected(old('exam_package_id',$exam->exam_package_id)==$p->id)>{{ $p->name }} ({{ $p->questions_count }} soal)</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label">Durasi (menit) <span class="text-danger">*</span></label><input type="number" name="duration_minutes" value="{{ old('duration_minutes',$exam->duration_minutes ?? 60) }}" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label">Nilai Minimum (KKM) <span class="text-danger">*</span></label><input type="number" step="0.1" name="passing_score" value="{{ old('passing_score',$exam->passing_score ?? 70) }}" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Tahun Ajaran</label><select name="academic_year_id" class="form-select"><option value="">—</option>@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(old('academic_year_id',$exam->academic_year_id)==$y->id)>{{ $y->name }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label">Semester</label><select name="semester_id" class="form-select"><option value="">—</option>@foreach($academicYears as $y)@foreach($y->semesters as $sem)<option value="{{ $sem->id }}" @selected(old('semester_id',$exam->semester_id)==$sem->id)>{{ $y->name }} - {{ $sem->name }}</option>@endforeach @endforeach</select></div>
            <div class="col-12"><label class="form-label">Deskripsi / Petunjuk</label><textarea name="description" rows="2" class="form-control">{{ old('description',$exam->description) }}</textarea></div>
            <div class="col-md-4"><div class="form-check form-switch"><input type="checkbox" name="randomize_questions" value="1" class="form-check-input" id="rq" @checked(old('randomize_questions',$exam->randomize_questions ?? true))><label class="form-check-label" for="rq">Acak soal</label></div></div>
            <div class="col-md-4"><div class="form-check form-switch"><input type="checkbox" name="randomize_options" value="1" class="form-check-input" id="ro" @checked(old('randomize_options',$exam->randomize_options ?? true))><label class="form-check-label" for="ro">Acak pilihan</label></div></div>
            <div class="col-md-4"><div class="form-check form-switch"><input type="checkbox" name="show_result" value="1" class="form-check-input" id="sr" @checked(old('show_result',$exam->show_result ?? false))><label class="form-check-label" for="sr">Publikasikan nilai ke siswa</label></div></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button></div>
    </form>
</div></div>
@endsection
