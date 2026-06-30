@extends('layouts.app')
@section('title', 'Detail Hasil')

@php $show = $result->examSchedule->exam->show_result; @endphp

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <a href="{{ route('siswa.results.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">Hasil Ujian</h1>
</div>

<div class="card mb-4" data-aos="fade-up"><div class="card-body p-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4 class="fw-bold mb-1">{{ $result->examSchedule->exam->title }}</h4>
            <p class="text-muted mb-0">{{ $result->examSchedule->exam->subject->name ?? '' }} · Dikumpulkan {{ $result->submitted_at?->format('d M Y H:i') }}</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @if($show && $result->status==='graded')
                <div class="display-4 fw-bold {{ $result->is_passed?'text-success':'text-danger' }}">{{ $result->total_score }}</div>
                <span class="badge bg-soft-{{ $result->is_passed?'success':'danger' }} fs-6">{{ $result->is_passed?'LULUS':'TIDAK LULUS' }}</span>
            @else
                <div class="display-6 text-muted">Menunggu</div>
                <small class="text-muted">Nilai belum dipublikasikan</small>
            @endif
        </div>
    </div>
    @if($show && $result->status==='graded')
    <div class="row g-3 mt-2">
        <div class="col-4"><div class="border rounded p-3 text-center"><div class="fw-bold text-success fs-4">{{ $result->correct_count }}</div><small class="text-muted">Benar</small></div></div>
        <div class="col-4"><div class="border rounded p-3 text-center"><div class="fw-bold text-danger fs-4">{{ $result->wrong_count }}</div><small class="text-muted">Salah</small></div></div>
        <div class="col-4"><div class="border rounded p-3 text-center"><div class="fw-bold text-muted fs-4">{{ $result->empty_count }}</div><small class="text-muted">Kosong</small></div></div>
    </div>
    @endif
</div></div>

@if($show && $result->status==='graded')
<div class="card" data-aos="fade-up"><div class="card-body p-4">
    <h6 class="fw-bold mb-3">Review Jawaban</h6>
    @foreach($result->answers as $i => $a)
        @php $q = $a->question; @endphp
        <div class="border rounded p-3 mb-2">
            <div class="d-flex justify-content-between">
                <span class="fw-semibold">Soal {{ $i+1 }}</span>
                @if($a->is_correct === true)<span class="badge bg-soft-success">Benar (+{{ $a->score }})</span>
                @elseif($a->is_correct === false)<span class="badge bg-soft-danger">Salah</span>
                @else<span class="badge bg-soft-secondary">{{ $a->graded ? $a->score.' poin' : 'Belum dikoreksi' }}</span>@endif
            </div>
            <div class="mt-1">{!! nl2br(e(Str::limit($q->question_text ?? '', 200))) !!}</div>
            <small class="text-muted d-block mt-1">Jawaban Anda: <strong>{{ is_array($a->answer) ? implode(', ', $a->answer) : '—' }}</strong></small>
            @if($q && $q->explanation)<small class="text-info d-block"><i class="bi bi-lightbulb me-1"></i>{{ $q->explanation }}</small>@endif
        </div>
    @endforeach
</div></div>
@else
    <div class="alert alert-info"><i class="bi bi-info-circle me-1"></i>Detail jawaban akan tersedia setelah guru mempublikasikan nilai.</div>
@endif
@endsection
