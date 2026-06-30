@extends('layouts.app')
@section('title', $package->exists ? 'Edit Paket' : 'Buat Paket')

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('guru.packages.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $package->exists ? 'Edit' : 'Buat' }} Paket Soal</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ $package->exists ? route('guru.packages.update',$package) : route('guru.packages.store') }}">
    @csrf @if($package->exists) @method('PUT') @endif
    <div class="card mb-3" data-aos="fade-up"><div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nama Paket <span class="text-danger">*</span></label><input name="name" value="{{ old('name',$package->name) }}" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                <select name="subject_id" class="form-select" required {{ $package->exists ? 'disabled' : '' }}>
                    <option value="">— Pilih —</option>@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(old('subject_id',$package->subject_id)==$s->id)>{{ $s->name }}</option>@endforeach
                </select>
                @if($package->exists)<input type="hidden" name="subject_id" value="{{ $package->subject_id }}">@endif
            </div>
            <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="description" rows="2" class="form-control">{{ old('description',$package->description) }}</textarea></div>
            <div class="col-md-6"><div class="form-check form-switch"><input type="checkbox" name="randomize_questions" value="1" class="form-check-input" id="rq" @checked(old('randomize_questions',$package->randomize_questions ?? true))><label class="form-check-label" for="rq">Acak urutan soal</label></div></div>
            <div class="col-md-6"><div class="form-check form-switch"><input type="checkbox" name="randomize_options" value="1" class="form-check-input" id="ro" @checked(old('randomize_options',$package->randomize_options ?? true))><label class="form-check-label" for="ro">Acak pilihan jawaban</label></div></div>
        </div>
    </div></div>

    @if($package->exists)
    <div class="card" data-aos="fade-up"><div class="card-body p-4">
        <h6 class="fw-bold mb-3">Pilih Soal untuk Paket Ini</h6>
        @if(count($available ?? []) === 0)
            <div class="alert alert-warning small">Belum ada soal pada bank soal mapel ini. <a href="{{ route('guru.question-banks.index') }}">Buat soal dulu</a>.</div>
        @else
            <div class="mb-2"><span class="badge bg-soft-primary" id="countBadge">0 soal dipilih</span></div>
            <div style="max-height:420px;overflow:auto" class="border rounded p-2">
                @foreach($available as $q)
                <label class="d-flex gap-2 align-items-start border-bottom py-2">
                    <input type="checkbox" name="questions[]" value="{{ $q->id }}" class="form-check-input mt-1 qcheck" @checked(in_array($q->id, $selected ?? []))>
                    <div>
                        <div class="small"><span class="badge bg-light text-dark me-1">{{ $q->type_label }}</span><span class="badge bg-light text-dark">{{ $q->score }} poin</span></div>
                        <div>{{ Str::limit($q->question_text, 120) }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        @endif
        <div class="mt-3"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Paket</button></div>
    </div></div>
    @else
    <button class="btn btn-primary"><i class="bi bi-arrow-right me-1"></i>Lanjut Pilih Soal</button>
    @endif
</form>
@endsection

@push('scripts')
<script>
const checks = document.querySelectorAll('.qcheck');
function updateCount(){ document.getElementById('countBadge').textContent = document.querySelectorAll('.qcheck:checked').length + ' soal dipilih'; }
checks.forEach(c => c.addEventListener('change', updateCount));
if (checks.length) updateCount();
</script>
@endpush
