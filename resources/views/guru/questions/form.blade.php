@extends('layouts.app')
@section('title', $question->exists ? 'Edit Soal' : 'Tambah Soal')

@php
    $type = old('type', $question->type ?? 'multiple_choice');
    $opts = $question->options ?? [];
    $correct = $question->correct_answer ?? [];
@endphp

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('guru.question-banks.questions.index',$questionBank) }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">{{ $question->exists ? 'Edit' : 'Tambah' }} Soal</h1>
</div>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form method="POST" action="{{ $question->exists ? route('guru.questions.update',$question) : route('guru.question-banks.questions.store',$questionBank) }}" enctype="multipart/form-data">
    @csrf @if($question->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card" data-aos="fade-up"><div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label">Jenis Soal</label>
                    <select name="type" id="qtype" class="form-select" onchange="toggleType()">
                        @foreach(\App\Models\Question::TYPES as $val => $label)
                            <option value="{{ $val }}" @selected($type===$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Pertanyaan <span class="text-danger">*</span></label><textarea name="question_text" rows="3" class="form-control" required>{{ old('question_text',$question->question_text) }}</textarea></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Gambar (opsional)</label><input type="file" name="image" class="form-control" accept="image/*">@if($question->image)<img src="{{ \Illuminate\Support\Facades\Storage::url($question->image) }}" class="mt-2 rounded" style="height:60px">@endif</div>
                    <div class="col-md-6 mb-3"><label class="form-label">Audio (opsional)</label><input type="file" name="audio" class="form-control" accept="audio/*"></div>
                </div>

                {{-- Multiple choice --}}
                <div class="type-block" data-type="multiple_choice">
                    <label class="form-label">Pilihan Jawaban <small class="text-muted">(pilih radio untuk jawaban benar)</small></label>
                    <div id="mcOptions">
                        @php $mc = $type==='multiple_choice' && $opts ? $opts : [['text'=>''],['text'=>'']]; @endphp
                        @foreach($mc as $i => $o)
                        <div class="input-group mb-2 mc-row">
                            <span class="input-group-text"><input type="radio" name="correct_option" value="{{ $i }}" @checked($type==='multiple_choice' && in_array($o['key'] ?? chr(65+$i), $correct))></span>
                            <input type="text" name="option_text[]" value="{{ $o['text'] ?? '' }}" class="form-control" placeholder="Opsi {{ chr(65+$i) }}">
                            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.mc-row').remove()"><i class="bi bi-x"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOption()"><i class="bi bi-plus"></i> Tambah Opsi</button>
                </div>

                {{-- True / false --}}
                <div class="type-block" data-type="true_false">
                    <label class="form-label">Jawaban Benar</label>
                    <select name="correct_tf" class="form-select">
                        <option value="true" @selected($type==='true_false' && in_array('true',$correct))>Benar</option>
                        <option value="false" @selected($type==='true_false' && in_array('false',$correct))>Salah</option>
                    </select>
                </div>

                {{-- Short answer --}}
                <div class="type-block" data-type="short_answer">
                    <label class="form-label">Kunci Jawaban <small class="text-muted">(pisahkan alternatif dengan tanda |)</small></label>
                    <input type="text" name="correct_text" value="{{ $type==='short_answer' ? implode('|',$correct) : '' }}" class="form-control" placeholder="contoh: Jakarta|DKI Jakarta">
                </div>

                {{-- Matching --}}
                <div class="type-block" data-type="matching">
                    <label class="form-label">Pasangan Menjodohkan</label>
                    <div id="matchRows">
                        @php $mt = $type==='matching' && $opts ? $opts : [['left'=>'','right'=>''],['left'=>'','right'=>'']]; @endphp
                        @foreach($mt as $p)
                        <div class="row g-2 mb-2 match-row">
                            <div class="col-5"><input name="match_left[]" value="{{ $p['left'] ?? '' }}" class="form-control" placeholder="Pernyataan"></div>
                            <div class="col-1 text-center pt-2"><i class="bi bi-arrow-right"></i></div>
                            <div class="col-5"><input name="match_right[]" value="{{ $p['right'] ?? '' }}" class="form-control" placeholder="Pasangan"></div>
                            <div class="col-1"><button type="button" class="btn btn-outline-danger" onclick="this.closest('.match-row').remove()"><i class="bi bi-x"></i></button></div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMatch()"><i class="bi bi-plus"></i> Tambah Pasangan</button>
                </div>

                <div class="type-block" data-type="essay"><div class="alert alert-info small mb-0"><i class="bi bi-info-circle me-1"></i>Soal essay dikoreksi manual oleh guru.</div></div>
                <div class="type-block" data-type="file_upload"><div class="alert alert-info small mb-0"><i class="bi bi-info-circle me-1"></i>Siswa mengunggah file; dikoreksi manual oleh guru.</div></div>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card" data-aos="fade-up" data-aos-delay="100"><div class="card-body p-4">
                <h6 class="fw-bold mb-3">Pengaturan</h6>
                <div class="mb-3"><label class="form-label">Bobot Nilai <span class="text-danger">*</span></label><input type="number" step="0.5" name="score" value="{{ old('score',$question->score ?? 1) }}" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Tingkat Kesulitan</label>
                    <select name="difficulty" class="form-select">
                        @foreach(['easy'=>'Mudah','medium'=>'Sedang','hard'=>'Sulit'] as $v=>$l)<option value="{{ $v }}" @selected(old('difficulty',$question->difficulty ?? 'medium')===$v)>{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Pembahasan (opsional)</label><textarea name="explanation" rows="3" class="form-control">{{ old('explanation',$question->explanation) }}</textarea></div>
                <button class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Simpan Soal</button>
            </div></div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function toggleType() {
    const t = document.getElementById('qtype').value;
    document.querySelectorAll('.type-block').forEach(b => b.style.display = b.dataset.type === t ? 'block' : 'none');
}
let optCount = document.querySelectorAll('#mcOptions .mc-row').length;
function addOption() {
    const i = optCount++;
    const div = document.createElement('div');
    div.className = 'input-group mb-2 mc-row';
    div.innerHTML = `<span class="input-group-text"><input type="radio" name="correct_option" value="${i}"></span>
        <input type="text" name="option_text[]" class="form-control" placeholder="Opsi ${String.fromCharCode(65+i)}">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.mc-row').remove()"><i class="bi bi-x"></i></button>`;
    document.getElementById('mcOptions').appendChild(div);
}
function addMatch() {
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 match-row';
    div.innerHTML = `<div class="col-5"><input name="match_left[]" class="form-control" placeholder="Pernyataan"></div>
        <div class="col-1 text-center pt-2"><i class="bi bi-arrow-right"></i></div>
        <div class="col-5"><input name="match_right[]" class="form-control" placeholder="Pasangan"></div>
        <div class="col-1"><button type="button" class="btn btn-outline-danger" onclick="this.closest('.match-row').remove()"><i class="bi bi-x"></i></button></div>`;
    document.getElementById('matchRows').appendChild(div);
}
toggleType();
</script>
@endpush
