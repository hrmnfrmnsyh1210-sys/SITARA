@extends('layouts.app')
@section('title', 'Soal · ' . $questionBank->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('guru.question-banks.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="page-title">{{ $questionBank->name }}</h1>
            <small class="text-muted">{{ $questionBank->subject->name ?? '' }} · {{ $questions->total() }} soal</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('guru.question-banks.questions.import',$questionBank) }}" class="btn btn-outline-primary"><i class="bi bi-file-earmark-word me-1"></i>Import Word</a>
        <a href="{{ route('guru.question-banks.questions.create',$questionBank) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Soal</a>
    </div>
</div>

@if(session('import_errors') && count(session('import_errors')))
    <div class="alert alert-warning" data-aos="fade-up">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Beberapa soal dilewati saat import:</div>
        <ul class="mb-0 small">@foreach(session('import_errors') as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

<div class="card" data-aos="fade-up"><div class="card-body">
    @forelse($questions as $i => $q)
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between">
                <div class="d-flex gap-2 mb-2">
                    <span class="badge bg-soft-primary">{{ $q->type_label }}</span>
                    <span class="badge bg-soft-{{ ['easy'=>'success','medium'=>'warning','hard'=>'danger'][$q->difficulty] }}">{{ ucfirst($q->difficulty) }}</span>
                    <span class="badge bg-light text-dark">{{ $q->score }} poin</span>
                </div>
                <div>
                    <a href="{{ route('guru.questions.edit',$q) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('guru.questions.destroy',$q) }}" method="POST" class="d-inline" data-confirm="delete" data-confirm-text="Soal ini akan dihapus.">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </div>
            </div>
            <div class="fw-semibold mb-1">{{ $questions->firstItem() + $i }}. {!! nl2br(e(Str::limit($q->question_text, 200))) !!}</div>
            @if($q->image)<img src="{{ \Illuminate\Support\Facades\Storage::url($q->image) }}" class="rounded mb-2" style="max-height:120px">@endif
            @if(in_array($q->type,['multiple_choice','true_false']) && $q->options)
                <div class="row g-1 small">
                    @foreach($q->options as $opt)
                        <div class="col-md-6">
                            <span class="{{ in_array($opt['key'], $q->correct_answer ?? []) ? 'text-success fw-bold' : 'text-muted' }}">
                                <i class="bi bi-{{ in_array($opt['key'], $q->correct_answer ?? []) ? 'check-circle-fill' : 'circle' }} me-1"></i>{{ $opt['text'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @elseif($q->type==='short_answer')
                <small class="text-success"><i class="bi bi-check-circle me-1"></i>Jawaban: {{ implode(' / ', $q->correct_answer ?? []) }}</small>
            @elseif(in_array($q->type,['essay','file_upload']))
                <small class="text-muted"><i class="bi bi-pencil me-1"></i>Dikoreksi manual oleh guru</small>
            @endif
        </div>
    @empty
        <div class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Belum ada soal. Klik "Tambah Soal".</div>
    @endforelse
    {{ $questions->links() }}
</div></div>
@endsection
